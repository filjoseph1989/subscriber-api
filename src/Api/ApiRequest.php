<?php

namespace Api;

use Api\Contracts\RequestHandlerInterface;
use Factory\RequestHandleFactory;

class ApiRequest
{
    private $requestHandleFactory;

    /**
     * Instantiate the ApiRequest class
     * @param mixed $resourceService
     */
    public function __construct(RequestHandleFactory $requestHandleFactory)
    {
        $this->requestHandleFactory = $requestHandleFactory;
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

        $handler = $this->requestHandleFactory->getHandler($method);

        if ($handler instanceof RequestHandlerInterface) {
            $response = $handler->handle($uriSegments);
            if (!is_null($response)) {
                echo $response;
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Route not found']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
        }
    }
}