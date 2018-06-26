<?php

namespace MusicBrainz\HttpAdapters;

use GuzzleHttp\ClientInterface;
use MusicBrainz\Exception;

/**
 * Guzzle Http Adapter
 */
class GuzzleHttpAdapter extends AbstractHttpAdapter
{
    /**
     * The Guzzle client used to make cURL requests
     *
     * @var \Guzzle\Http\ClientInterface
     */
    private $client;

    /**
     * Initializes the class.
     *
     * @param \Guzzle\Http\ClientInterface $client The Guzzle client used to make requests
     * @param null                         $endpoint Override the default endpoint (useful for local development)
     */
    public function __construct(ClientInterface $client, $endpoint = null)
    {
        $this->client = $client;

        if (filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $this->endpoint = $endpoint;
        }
    }

    /**
     * Perform an HTTP request on MusicBrainz
     *
     * @param  string  $path
     * @param  array   $params
     * @param  array   $options
     * @param  boolean $isAuthRequired
     * @param  boolean $returnArray disregarded
     *
     * @throws \MusicBrainz\Exception
     * @return array
     */
    public function call($path, array $params = array(), array $options = array(), $isAuthRequired = false, $returnArray = false)
    {
        if ($options['user-agent'] == '') {
            throw new Exception('You must set a valid User Agent before accessing the MusicBrainz API');
        }

        $this->client->setBaseUrl($this->endpoint);
        $this->client->setConfig(
            array_merge(
                $this->client->getConfig()->toArray(),
                array(
                    'data' => $params
                )
            )
        );

        //$request = $this->client->get($path . '{?data*}');
        //$request->setHeader('Accept', 'application/json');
        //$request->setHeader('User-Agent', $options['user-agent']);

        if ($isAuthRequired) {
            if ($options['user'] != null && $options['password'] != null) {
                $request->setAuth($options['user'], $options['password'], CURLAUTH_DIGEST);
            } else {
                throw new \Exception('Authentication is required');
            }
        }

        print_r($this->client);

        $response = $this->client->request('GET', $path .  '{?data*}', [
            'headers' => [
                'Accept'        => 'application/json',
                'User-Agent'    => $options['user-agent']
            ]
        ]);

        print_r($response);


        //$request->getQuery()->useUrlEncoding(false);

        // musicbrainz throttle
        sleep(1);

        $body = $response->getBody();
        return $body;
        //return $request->send()->json();
    }
}