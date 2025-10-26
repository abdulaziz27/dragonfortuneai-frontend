<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$controller = app(App\Http\Controllers\SpotMicrostructureController::class);
$request = new Illuminate\Http\Request(['symbol' => 'BTCUSDT', 'limit' => 360]);
$response = $controller->getCvd($request);
echo json_encode(['count' => count($response->getData(true)['data'] ?? [])]);
