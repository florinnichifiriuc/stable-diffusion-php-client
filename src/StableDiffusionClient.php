<?php

namespace Florinnichifiriuc\StableDiffusionPhpClient;

use GuzzleHttp\Client;

class StableDiffusionClient
{
    protected $apiUrl;
    protected $client;
    private $model = null;

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
        if ($this->model !== null) {
            // Add 'sd_model_checkpoint' to params only if it's not already set
            if (!isset($params['sd_model_checkpoint'])) {
                $params['sd_model_checkpoint'] = $this->model;
            }
        }

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

    public function setModel($modelTitle)
    {
        // Get the list of available models
        $models = $this->getModels();

        // Check if the model exists
        $modelExists = false;
        foreach ($models as $model) {
            if ($model['title'] === $modelTitle) {
                $modelExists = true;
                break;
            }
        }

        if ($modelExists) {
            $this->model = $modelTitle;
        } else {
            throw new \InvalidArgumentException('Model not valid: ' . $modelTitle);
        }
    }

    // Additional methods can be added here
}
