<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Controllers/HomeController.php';

use Controllers\HomeController;
use Controllers\SubscriberController;

// Set load environment variables from .env file
$rootDir = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->safeLoad();

$router = new Router();

// Define routes
$router->addRoute('GET', '/', HomeController::class, 'index');
$router->addRoute('GET', '/ims/subscriber/{phoneNumber}', SubscriberController::class, 'getSubscriber');
$router->addRoute('POST', '/ims/subscriber', SubscriberController::class, 'addSubscriber');
$router->addRoute('PUT', '/ims/subscriber', SubscriberController::class, 'updateSubscriber');
$router->addRoute('DELETE', '/ims/subscriber/{phoneNumber}', SubscriberController::class, 'deleteSubscriber');

// Dispatch the request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);