<?php

namespace Api;

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
                if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && !isset($uriSegments[2])) {
                    $handleRequest = function ($data) {
                        $resources = $this->resourceService->getResources($_ENV['RESOURCES']);
                        if (!$this->validationService->validateSubscriberData($data)) {
                            http_response_code($this->validationService->getResponseStatus());
                            return json_encode([
                                'message' => $this->validationService->getResponse()
                            ]);
                        }

                        // Hash the password before storing it
                        $hashedPassword = password_hash($data["password"], PASSWORD_BCRYPT);
                        $data["password"] = $hashedPassword;

                        // Add the new subscriber to the resources array
                        if (is_null($resources)) {
                            $resources = [];
                            $resources[] = $data;
                        } else if (!is_null($resources) && is_array($resources)) {
                            foreach ($resources as $resource) {
                                if ($resource["phoneNumber"] == $data["phoneNumber"]) {
                                    return json_encode([
                                        'message' => "The subscriber {$data["phoneNumber"]} already exists"
                                    ]);
                                } else {
                                    $resources[] = $data;
                                }
                            }
                        }

                        $response = $this->resourceService->addResource($_ENV['RESOURCES'], $resources);

                        if ($response) {
                            http_response_code(201);
                            return json_encode([
                                'message' => 'Successfully added new subscriber'
                            ]);
                        }

                        http_response_code(404);
                        return json_encode([
                            'message' => 'Failed to add new subscriber'
                        ]);
                    };

                    $requestBody = file_get_contents('php://input');
                    $data = json_decode($requestBody, true);

                    echo $handleRequest($data);
                } else {
                    $route = false;
                }
                break;

            case 'GET':
                if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
                    $phoneNumber = $uriSegments[2];
                    $handleRequest = function ($phoneNumber) {
                        $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

                        foreach ($resources as $resource) {
                            if ($resource['phoneNumber'] == $phoneNumber) {
                                if (isset($resource['password'])) {
                                    $resource['password'] = '';
                                }
                                http_response_code(200);
                                return json_encode($resource);
                            }
                        }

                        http_response_code(404);
                        return json_encode([
                            'message' => 'Contact not found'
                        ]);
                    };

                    echo $handleRequest($phoneNumber);
                } else {
                    $route = false;
                }
                break;

            case 'DELETE':
                if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
                    $phoneNumber = $uriSegments[2];
                    $handleRequest = function ($phoneNumber) {
                        $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

                        $isContactDeleted = false;
                        foreach ($resources as $key => $resource) {
                            if ($resource['phoneNumber'] == $phoneNumber) {
                                unset($resources[$key]);
                                $isContactDeleted = !$isContactDeleted;
                            }
                        }

                        if ($isContactDeleted) {
                            $response = $this->resourceService->updateResource($_ENV['RESOURCES'], array_values($resources));
                            if ($response) {
                                http_response_code(201);
                                return json_encode(['message' => 'Successfully deleted contact information']);
                            }
                        }

                        http_response_code(404);

                        return json_encode([
                            'message' => 'Contact not found'
                        ]);
                    };

                    echo $handleRequest($phoneNumber);
                } else {
                    $route = false;
                }
                break;

            case 'PUT':
                if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
                    $phoneNumber = $uriSegments[2];
                    $handleRequest = function ($phoneNumber, $data) {
                        foreach ($data as $item) {
                            if (!is_array($item)) {
                                http_response_code(400);
                                return json_encode([
                                    'message' => "Invalid entry, it must be collection of subscriber object wrap with []"
                                ]);
                            }
                            if (!$this->validationService->validateSubscriberData($item)) {
                                http_response_code($this->validationService->getResponseStatus());
                                return json_encode([
                                    'message' => $this->validationService->getResponse()
                                ]);
                            }
                        }

                        $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

                        foreach ($resources as &$resource) {
                            if ($resource['phoneNumber'] == $phoneNumber) {
                                $resource['username'] = $data[0]['username'];
                                $hashedPassword = password_hash($data[0]['password'], PASSWORD_BCRYPT);
                                $resource['password'] = $hashedPassword; // Todo: password must have salt as extra layer of security
                                $resource['domain'] = $data[0]['domain'];
                                $resource['status'] = strtoupper($data[0]['status']);
                                $resource["features"]["callForwardNoReply"]["provisioned"] = $data[0]["features"]["callForwardNoReply"]["provisioned"];
                                $resource["features"]["callForwardNoReply"]["destination"] = $data[0]["features"]["callForwardNoReply"]["destination"];
                            }
                        }

                        $response = $this->resourceService->updateResource($_ENV['RESOURCES'], $resources);

                        if ($response) {
                            http_response_code(200);
                            return json_encode([
                                'message' => 'Successfully updated contact information'
                            ]);
                        }

                        http_response_code(404);
                        return json_encode([
                            'message' => 'Failed to update contact information'
                        ]);
                    };

                    $requestBody = file_get_contents('php://input');
                    $data = json_decode($requestBody, true);

                    echo $handleRequest($phoneNumber, $data);
                } else {
                    $route = false;
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