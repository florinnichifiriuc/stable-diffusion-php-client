Certainly! Here's the complete `README.md` file in markdown format, ready for you to download and use:

---

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
    - [Laravel Integration](#laravel-integration)
        - [Controller Example](#controller-example)
        - [Blade Templates](#blade-templates)
        - [Routes](#routes)
- [Advanced Usage](#advanced-usage)
    - [Available Methods](#available-methods)
        - [`txt2img(array $params)`](#txt2imgarray-params)
        - [`getModels()`](#getmodels)
        - [`setModel($modelTitle)`](#setmodelmodeltitle)
    - [Custom Parameters](#custom-parameters)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- Interact with the Stable Diffusion API using PHP.
- Generate images from text prompts (`txt2img`).
- Retrieve and switch between available models.
- Flexible parameter configuration.
- Easy integration with Laravel applications.
- PSR-4 autoloading compliant.

## Requirements

- PHP 7.4 or higher.
- Composer.
- [Guzzle HTTP client](https://github.com/guzzle/guzzle) (automatically installed via Composer).
- Locally running Stable Diffusion instance with API enabled (using [Automatic1111's Web UI](https://github.com/AUTOMATIC1111/stable-diffusion-webui)).

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

1. **Install Stable Diffusion with Automatic1111 Web UI** if you haven't already. Follow the instructions on the [GitHub repository](https://github.com/AUTOMATIC1111/stable-diffusion-webui).

2. **Enable the API** by starting the Web UI with the `--api` flag:

   ```bash
   python launch.py --api
   ```

    - Alternatively, modify your startup scripts or batch files to include the `--api` flag.
    - By default, the API runs at `http://127.0.0.1:7860`.

## Configuration

### Environment Variables

In your project's `.env` file (or equivalent configuration), add the following line:

```env
STABLE_DIFFUSION_API_URL=http://127.0.0.1:7860
```

Adjust the URL if your Stable Diffusion API is running on a different host or port.

### Laravel Configuration (Optional)

If you're using Laravel, create a configuration file to manage Stable Diffusion settings.

**File:** `config/stable_diffusion.php`

```php
<?php

return [
    'api_url' => env('STABLE_DIFFUSION_API_URL', 'http://127.0.0.1:7860'),
];
```

## Usage

### Basic Example

```php
<?php

require 'vendor/autoload.php';

use Florinnichifiriuc\StableDiffusionPhpClient\StableDiffusionClient;

$sdClient = new StableDiffusionClient();

// Define parameters
$params = [
    'prompt' => 'A fantasy landscape with mountains and a river',
    'negative_prompt' => 'low quality, blurry',
    'steps' => 50,
    'cfg_scale' => 7.5,
];

// Generate image
$images = $sdClient->txt2img($params);

if ($images) {
    // Decode the base64 image
    $decodedImage = base64_decode($images[0]);

    // Save the image to a file
    file_put_contents('generated_image.png', $decodedImage);

    echo "Image generated successfully: generated_image.png";
} else {
    echo "Failed to generate image.";
}
```

### Using Models

You can retrieve available models and set a specific model for image generation.

#### Get Available Models

```php
$sdClient = new StableDiffusionClient();

try {
    $models = $sdClient->getModels();

    if ($models) {
        echo "Available Models:\n";
        foreach ($models as $model) {
            echo "- " . $model['title'] . "\n";
        }
    } else {
        echo "No models found.";
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

#### Set a Model

```php
try {
    $desiredModelTitle = 'v1-5-pruned-emaonly'; // Replace with your desired model title
    $sdClient->setModel($desiredModelTitle);
} catch (\InvalidArgumentException $e) {
    echo 'Model Error: ' . $e->getMessage();
}
```

#### Generate Image with Selected Model

```php
$params = [
    'prompt' => 'A beautiful sunset over the mountains',
    'negative_prompt' => 'low quality, blurry',
    'steps' => 30,
    'cfg_scale' => 7.5,
];

$images = $sdClient->txt2img($params);
```

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
    public function showForm()
    {
        return view('generate_image');
    }

    public function generateImage(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'negative_prompt' => 'nullable|string|max:1000',
            'steps' => 'nullable|integer|min:1|max:150',
            'cfg_scale' => 'nullable|numeric|min:1|max:30',
            'seed' => 'nullable|integer',
            'model' => 'nullable|string',
        ]);

        $params = [
            'prompt' => $request->input('prompt'),
            'negative_prompt' => $request->input('negative_prompt', ''),
            'steps' => $request->input('steps', 20),
            'cfg_scale' => $request->input('cfg_scale', 7.5),
            'seed' => $request->input('seed'),
        ];

        $sdClient = new StableDiffusionClient();

        try {
            // Set model if provided
            if ($request->filled('model')) {
                $sdClient->setModel($request->input('model'));
            }

            $images = $sdClient->txt2img($params);

            if ($images) {
                $decodedImage = base64_decode($images[0]);

                // Save the image to storage (e.g., public disk)
                $filename = 'generated_' . time() . '.png';
                Storage::disk('public')->put($filename, $decodedImage);

                return view('image_result', [
                    'image_url' => Storage::url($filename),
                ]);
            } else {
                return back()->withErrors(['message' => 'Failed to generate image']);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['message' => 'Model Error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
```

#### Blade Templates

**a. Image Generation Form**

**File:** `resources/views/generate_image.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Generate Image</title>
</head>
<body>
    <h1>Generate Image</h1>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ url('/generate-image') }}" method="POST">
        @csrf
        <label for="prompt">Prompt:</label><br>
        <textarea id="prompt" name="prompt" required rows="4" cols="50">{{ old('prompt') }}</textarea><br><br>

        <label for="negative_prompt">Negative Prompt:</label><br>
        <textarea id="negative_prompt" name="negative_prompt" rows="2" cols="50">{{ old('negative_prompt') }}</textarea><br><br>

        <label for="steps">Steps (1-150):</label><br>
        <input type="number" id="steps" name="steps" value="{{ old('steps', 20) }}" min="1" max="150"><br><br>

        <label for="cfg_scale">CFG Scale (1-30):</label><br>
        <input type="number" id="cfg_scale" name="cfg_scale" step="0.5" value="{{ old('cfg_scale', 7.5) }}" min="1" max="30"><br><br>

        <label for="seed">Seed (Optional):</label><br>
        <input type="number" id="seed" name="seed" value="{{ old('seed') }}"><br><br>

        <label for="model">Model (Optional):</label><br>
        <input type="text" id="model" name="model" value="{{ old('model') }}"><br><br>

        <input type="submit" value="Generate Image">
    </form>
</body>
</html>
```

**b. Image Result Page**

**File:** `resources/views/image_result.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Generated Image</title>
</head>
<body>
    <h1>Generated Image</h1>

    <img src="{{ asset($image_url) }}" alt="Generated Image"><br><br>

    <a href="{{ url('/generate-image') }}">Generate Another Image</a>
</body>
</html>
```

#### Routes

**File:** `routes/web.php`

```php
<?php

use App\Http\Controllers\ImageGenerationController;
use Illuminate\Support\Facades\Route;

Route::get('/generate-image', [ImageGenerationController::class, 'showForm']);
Route::post('/generate-image', [ImageGenerationController::class, 'generateImage']);
```

#### Storage Setup

Ensure that your application has a symbolic link from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link
```

## Advanced Usage

### Available Methods

#### `txt2img(array $params)`

Generates images from text prompts.

- If you have set a model using `setModel()`, the model will be added to the `$params` array as `'sd_model_checkpoint'`, but only if it's not already set in `$params`.
- If `'sd_model_checkpoint'` is already set in `$params`, it will not be overridden by the model set via `setModel()`.

#### `getModels()`

Retrieves a list of all available Stable Diffusion models installed in your local instance.

**Usage Example:**

```php
$models = $sdClient->getModels();
```

**Response Format:**

The `getModels()` method returns an array of models, where each model is an associative array containing information about the model.

**Example Response:**

```php
[
    [
        'title' => 'v1-5-pruned-emaonly',
        'model_name' => 'v1-5-pruned-emaonly.ckpt',
        'hash' => 'abc123',
        'sha256' => '...',
        'filename' => '/path/to/v1-5-pruned-emaonly.ckpt',
        'config' => '/path/to/v1-5-pruned-emaonly.yaml',
    ],
    // Additional models...
]
```

#### `setModel($modelTitle)`

Sets the model to be used for image generation.

- Validates that the model exists.
- Stores the model in a private property for use in subsequent API calls.
- Does not override `'sd_model_checkpoint'` if it's already set in `$params`.

**Usage Example:**

```php
$sdClient->setModel('v1-5-pruned-emaonly');
```

**Important Note:**

- The inclusion of `'sd_model_checkpoint'` as a top-level parameter in the `txt2img` API call may depend on the specific implementation of your Stable Diffusion API.
- In the standard Automatic1111 API, model overrides are typically done via the `'override_settings'` parameter.
- **Please verify that your API supports `'sd_model_checkpoint'` as a top-level parameter.**

### Custom Parameters

The `txt2img` method accepts an associative array of parameters that correspond to the Stable Diffusion API's options.

**Common Parameters:**

- `prompt` (string): The text prompt for image generation.
- `negative_prompt` (string): Text prompts to exclude from the image.
- `steps` (integer): Number of inference steps (default is 20).
- `cfg_scale` (float): Classifier-Free Guidance scale (default is 7.5).
- `seed` (integer): Seed for random number generation (optional).
- `width` (integer): Width of the generated image.
- `height` (integer): Height of the generated image.
- `sampler_index` (string): The sampler to use for image generation.

**Example with Additional Parameters:**

```php
$params = [
    'prompt' => 'A futuristic cityscape at night',
    'negative_prompt' => 'low resolution, blurry',
    'steps' => 50,
    'cfg_scale' => 9.0,
    'width' => 512,
    'height' => 512,
    'sampler_index' => 'Euler',
];
```

Refer to the Stable Diffusion API documentation for a full list of available parameters.

## Error Handling

The library throws exceptions when:

- The specified model does not exist (`InvalidArgumentException`).
- HTTP requests fail or the API returns an error.

You should wrap API calls in try-catch blocks to handle exceptions gracefully.

**Example:**

```php
try {
    $sdClient->setModel('non-existent-model');
} catch (\InvalidArgumentException $e) {
    echo 'Model Error: ' . $e->getMessage();
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

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

3. **Run Tests:**

   ```bash
   composer test
   ```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.


