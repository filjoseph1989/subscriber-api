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
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $uriSegments = explode('/', $uri);

        $handler = new SubscriberHandler($this->resourceService, $this->validationService);

        $response = null;

        switch ($method) {
            case 'POST':
                $response = $handler->handlePost($uriSegments);
                break;

            case 'GET':
                $response = $handler->handleGet($uriSegments);
                break;

            case 'DELETE':
                $response = $handler->handleDelete($uriSegments);
                break;

            case 'PUT':
                $response = $handler->handlePut($uriSegments);
                break;

            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }

        if (!is_null($response)) {
            echo $response;
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
        }
    }
}