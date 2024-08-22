<?php

namespace Api;

class ApiRequest
{
    private $resourceService;

    /**
     * Instantiate the ApiRequest class
     * @param mixed $resourceService
     */
    public function __construct($resourceService)
    {
        $this->resourceService = $resourceService;
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

        if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
            $phoneNumber = $uriSegments[2];

            switch ($method) {
                case 'GET':
                    $handleRequest = function ($phoneNumber) {
                        $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

                        foreach ($resources as $resource) {
                            if ($resource['phoneNumber'] == $phoneNumber) {
                                return json_encode($resource);
                            }
                        }

                        http_response_code(404);
                        return json_encode([
                            'message' => 'Contact not found'
                        ]);
                    };

                    echo $handleRequest($phoneNumber);
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(['message' => 'Method not allowed']);
                    break;
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
        }
    }
}