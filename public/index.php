<?php

require __DIR__ . '/../vendor/autoload.php';

use Factory\ApiRequestFactory;

// Set load environment variables from .env file
$rootDir = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->safeLoad();

(new ApiRequestFactory())->create()->handleRequest();