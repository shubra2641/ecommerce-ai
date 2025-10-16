<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Http\Request;

// Build a dummy validator that fails
$validatorFactory = $app->make(ValidatorFactory::class);
$validator = $validatorFactory->make(['name' => ''], ['name' => 'required']);

try {
    throw new ValidationException($validator);
} catch (ValidationException $e) {
    $handler = $app->make(App\Exceptions\Handler::class);
    $request = Request::create('/', 'GET');
    $response = $handler->render($request, $e);
    echo "Response class: " . get_class($response) . "\n";
    echo "Status code: " . $response->getStatusCode() . "\n";
}
