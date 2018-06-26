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


        var_dump($path);
        var_dump($params);
        var_dump($options);


        $this->client->setBaseUrl($this->endpoint);
        //$this->client->setConfig(['json' => $params]);
        /*
        $this->client->setConfig(
            array_merge(
                $this->client->getConfig(),
                array(
                    'json' => $params
                )
            )
        );
        */

        print_r($this->client);

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

        $response = $this->client->request('GET', $path , [
            'json'      => $params,
            'headers'   => [
                'Accept'        => 'application/json',
                'User-Agent'    => $options['user-agent']
            ]
        ]);

        print_r($response);


        //$request->getQuery()->useUrlEncoding(false);

        // musicbrainz throttle
        sleep(1);

        $body = $response->getBody()->getContents();
        return $body;
        //return $request->send()->json();
    }
}
