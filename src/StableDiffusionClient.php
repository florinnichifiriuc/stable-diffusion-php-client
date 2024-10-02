<?php

namespace Florinnichifiriuc\StableDiffusionPhpClient;

use GuzzleHttp\Client;

class StableDiffusionClient
{
    protected $apiUrl;
    protected $client;

    public function __construct($apiUrl = null)
    {
        $this->apiUrl = rtrim($apiUrl ?? 'http://127.0.0.1:7860', '/');
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout'  => 300.0, // Adjust timeout as needed
        ]);
    }

    public function txt2img(array $params)
    {
        $response = $this->client->post('/sdapi/v1/txt2img', [
            'json' => $params,
        ]);

        $result = json_decode($response->getBody(), true);

        if (isset($result['images'])) {
            return $result['images'];
        }

        return null;
    }

    public function getModels()
    {
        $response = $this->client->get('/sdapi/v1/sd-models');
        $result = json_decode($response->getBody(), true);

        if (isset($result)) {
            return $result;
        }

        return null;
    }

    // You can add more methods like img2img, etc.
}
