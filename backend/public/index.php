<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
// Ensure cache dir exists in serverless runtime.
$bootstrapCache = __DIR__.'/../bootstrap/cache';
if (!is_dir($bootstrapCache)) {
    @mkdir($bootstrapCache, 0775, true);
}

// Register the Composer autoloader...
$autoloadPaths = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadPaths as $candidate) {
    if (file_exists($candidate)) {
        $autoloadFile = $candidate;
        break;
    }
}

if (!$autoloadFile) {
    http_response_code(500);
    echo 'Composer autoload not found. Expected one of: ' . implode(', ', $autoloadPaths);
    exit(1);
}

require $autoloadFile;

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture());
