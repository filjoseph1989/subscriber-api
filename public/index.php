<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Controllers/HomeController.php';

use Controllers\HomeController;
use Controllers\SubscriberController;
use Controllers\SubscriberJsonController;

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

// Routes to interact with JSON file as a storage
// $router->addRoute('GET', '/ims/subscriber/{phoneNumber}', SubscriberJsonController::class, 'getSubscriber');
// $router->addRoute('POST', '/ims/subscriber', SubscriberJsonController::class, 'addSubscriber');
// $router->addRoute('PUT', '/ims/subscriber', SubscriberJsonController::class, 'updateSubscriber');
// $router->addRoute('DELETE', '/ims/subscriber/{phoneNumber}', SubscriberJsonController::class, 'deleteSubscriber');

// Dispatch the request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);