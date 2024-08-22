<?php

require __DIR__ . '/../vendor/autoload.php';

use Api\ApiRequest;
use Services\ResourceService;
use Services\ValidationService;

// Set load environment variables from .env file
$rootDir = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->safeLoad();

(new ApiRequest(new ResourceService, new ValidationService))->handleRequest();