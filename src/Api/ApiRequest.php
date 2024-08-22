<?php

namespace Api;
use Api\Handler\SubscriberHandler;

class ApiRequest
{
    private $resourceService;
    private $validationService;

    /**
     * Instantiate the ApiRequest class
     * @param mixed $resourceService
     */
    public function __construct($resourceService, $validationService)
    {
        $this->resourceService = $resourceService;
        $this->validationService = $validationService;
    }

    /**
     * Handle the request
     * @return void
     */
    public function handleRequest()
    {
        $route = true;
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $uriSegments = explode('/', $uri);

        switch ($method) {
            case 'POST':
                $handler = new SubscriberHandler($this->resourceService, $this->validationService);
                $response = $handler->handlePost($uriSegments);

                if (is_null($response)) {
                    $route = false;
                } else {
                    echo $response;
                }
                break;

            case 'GET':
                $handler = new SubscriberHandler($this->resourceService, $this->validationService);
                $response = $handler->handleGet($uriSegments);
                if (is_null($response)) {
                    $route = false;
                } else {
                    echo $response;
                }
                break;

            case 'DELETE':
                $handler = new SubscriberHandler($this->resourceService, $this->validationService);
                $response = $handler->handleDelete($uriSegments);
                if (is_null($response)) {
                    $route = false;
                } else {
                    echo $response;
                }
                break;

            case 'PUT':
                $handler = new SubscriberHandler($this->resourceService, $this->validationService);
                $response = $handler->handlePut($uriSegments);
                if (is_null($response)) {
                    $route = false;
                } else {
                    echo $response;
                }
                break;

            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }

        if ($route === false) {
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
        }
    }
}