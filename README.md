# Stable Diffusion PHP Client

A PHP client library for interacting with the [Stable Diffusion](https://github.com/AUTOMATIC1111/stable-diffusion-webui) API. This library allows you to integrate Stable Diffusion's text-to-image generation capabilities into your PHP applications, including Laravel projects.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
    - [1. Install via Composer](#1-install-via-composer)
    - [2. Set Up Stable Diffusion API](#2-set-up-stable-diffusion-api)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Basic Example](#basic-example)
    - [Using Models](#using-models)
    - [Additional Methods](#additional-methods)
    - [Laravel Integration](#laravel-integration)
- [Advanced Usage](#advanced-usage)
    - [Available Methods](#available-methods)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- Interact with the Stable Diffusion API using PHP.
- Generate images from text prompts (`txt2img`).
- Generate images from existing images (`img2img`).
- Inpainting support with masking (`inpaint`).
- Retrieve available models and samplers.
- Asynchronous image generation (`txt2imgAsync`).
- Progress tracking during image generation.
- Fetch and update configurations.

## Requirements

- PHP 7.4 or higher.
- Composer.
- [Guzzle HTTP client](https://github.com/guzzle/guzzle) (automatically installed via Composer).
- Locally running Stable Diffusion instance with API enabled.

## Installation

### 1. Install via Composer

Run the following command in your project's root directory:

```bash
composer require florinnichifiriuc/stable-diffusion-php-client
```

Replace `florinnichifiriuc/stable-diffusion-php-client` with the actual package name as published on Packagist.

### 2. Set Up Stable Diffusion API

Ensure that your Stable Diffusion instance is running with the API enabled.

#### Steps:

1. **Install Stable Diffusion with Automatic1111 Web UI** if you haven't already.
2. **Enable the API** by starting the Web UI with the `--api` flag:

   ```bash
   python launch.py --api
   ```

    - By default, the API runs at `http://127.0.0.1:7860`.

## Configuration

### Environment Variables

In your project's `.env` file (or equivalent configuration), add the following line:

```env
STABLE_DIFFUSION_API_URL=http://127.0.0.1:7860
```

## Usage

### Basic Example

```php
<?php

require 'vendor/autoload.php';

use Florinnichifiriuc\StableDiffusionPhpClient\StableDiffusionClient;

$sdClient = new StableDiffusionClient();

$params = [
    'prompt' => 'A beautiful sunset over the mountains',
    'steps' => 30,
    'cfg_scale' => 7.5,
];

$images = $sdClient->txt2img($params);

if ($images) {
    file_put_contents('generated_image.png', base64_decode($images[0]));
}
```

### Using Models

```php
$sdClient->setModel('v1-5-pruned-emaonly');
```

### Additional Methods

- `img2img()` – Create images from existing images.
- `inpaint()` – Perform inpainting using masks.
- `txt2imgAsync()` – Perform asynchronous image generation.
- `getProgress()` – Fetch progress of image generation.
- `getConfig()` – Fetch current configuration.
- `setConfig()` – Set configurations like default resolution or sampler.
- `fetchResultByTaskId()` – Get the result of async image generation using task ID.

### Laravel Integration

#### Controller Example

**File:** `app/Http/Controllers/ImageGenerationController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Florinnichifiriuc\StableDiffusionPhpClient\StableDiffusionClient;
use Illuminate\Support\Facades\Storage;

class ImageGenerationController extends Controller
{
    public function generateImage(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'steps' => 'nullable|integer|min:1|max:150',
            'cfg_scale' => 'nullable|numeric|min:1|max:30',
        ]);

        $params = [
            'prompt' => $request->input('prompt'),
            'steps' => $request->input('steps', 20),
            'cfg_scale' => $request->input('cfg_scale', 7.5),
        ];

        $sdClient = new StableDiffusionClient();

        try {
            $images = $sdClient->txt2img($params);

            if ($images) {
                $decodedImage = base64_decode($images[0]);

                // Save the image to storage (e.g., public disk)
                $filename = 'generated_' . time() . '.png';
                Storage::disk('public')->put($filename, $decodedImage);

                return response()->json(['image_url' => Storage::url($filename)], 200);
            } else {
                return response()->json(['error' => 'Failed to generate image'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## Available Methods

- `txt2img(array $params)` – Generates images from text prompts.
- `img2img(array $params, string $inputImage)` – Generates images from an existing input image.
- `inpaint(array $params, string $inputImage, string $maskImage)` – Performs inpainting on an image using a mask.
- `txt2imgAsync(array $params)` – Asynchronously generates images and returns a task ID.
- `fetchResultByTaskId(string $taskId)` – Fetches the result of asynchronous image generation.
- `getModels()` – Retrieves the available models installed.
- `setModel($modelTitle)` – Sets the model for image generation.
- `getSamplers()` – Fetches available samplers.
- `getUpscalers()` – Fetches available upscalers.
- `getProgress()` – Retrieves progress for ongoing image generation.
- `getConfig()` – Fetches current configuration settings.
- `setConfig(array $config)` – Updates the configuration settings (e.g., default resolution, sampler).

## Error Handling

The library throws exceptions when:
- The specified model does not exist (`InvalidArgumentException`).
- HTTP requests fail or the API returns an error.

## Contributing

Contributions are welcome! If you encounter bugs or have feature requests, please open an issue or submit a pull request on GitHub.

### Development Setup

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/florinnichifiriuc/stable-diffusion-php-client.git
   ```

2. **Install Dependencies:**

   ```bash
   composer install
   ```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.