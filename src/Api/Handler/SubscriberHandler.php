<?php

namespace Api\Handler;

use Services\ResourceService;
use Services\ValidationService;

class SubscriberHandler
{
    private $resourceService;
    private $validationService;

    public function __construct(ResourceService $resourceService, ValidationService $validationService)
    {
        $this->resourceService = $resourceService;
        $this->validationService = $validationService;
    }

    public function handlePost(array $uriSegments): ?string
    {
        if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && !isset($uriSegments[2])) {
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            if (!$this->validationService->validateSubscriberData($data)) {
                http_response_code($this->validationService->getResponseStatus());
                return $this->validationService->getResponse();
            }

            // Hash the password before storing it
            $hashedPassword = password_hash($data["password"], PASSWORD_BCRYPT);
            $data["password"] = $hashedPassword;

            // Add the new subscriber to the resources array
            $resources = $this->resourceService->getResources($_ENV['RESOURCES']);
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
        }

        return null;
    }

    public function handleGet(array $uriSegments): ?string
    {
        if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
            $phoneNumber = $uriSegments[2];
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
        }

        return null;
    }

    public function handleDelete(array $uriSegments): ?string
    {
        if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
            $phoneNumber = $uriSegments[2];
            $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

            $isContactDeleted = false;
            foreach ($resources as $key => $resource) {
                if ($resource['phoneNumber'] == $phoneNumber) {
                    unset($resources[$key]);
                    $isContactDeleted = true;
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
        }

        return null;
    }

    public function handlePut(array $uriSegments): ?string
    {
        if ($uriSegments[0] === 'ims' && $uriSegments[1] === 'subscriber' && isset($uriSegments[2])) {
            $phoneNumber = $uriSegments[2];
            $requestBody = file_get_contents('php://input');
            $data = json_decode($requestBody, true);

            foreach ($data as $item) {
                if (!is_array($item)) {
                    http_response_code(400);
                    return json_encode(['message' => "Invalid entry, it must be collection of subscriber object wrap with []"]);
                }
                if (!$this->validationService->validateSubscriberData($item)) {
                    http_response_code($this->validationService->getResponseStatus());
                    return $this->validationService->getResponse();
                }
            }

            $resources = $this->resourceService->getResources($_ENV['RESOURCES']);

            $found = false;

            foreach ($resources as &$resource) {
                if ($resource['phoneNumber'] == $phoneNumber) {
                    $found = true;
                    $resource['username'] = $data[0]['username'];
                    $hashedPassword = password_hash($data[0]['password'], PASSWORD_BCRYPT);
                    $resource['password'] = $hashedPassword; // Todo: password must have salt as extra layer of security
                    $resource['domain'] = $data[0]['domain'];
                    $resource['status'] = strtoupper($data[0]['status']);
                    $resource["features"]["callForwardNoReply"]["provisioned"] = $data[0]["features"]["callForwardNoReply"]["provisioned"];
                    $resource["features"]["callForwardNoReply"]["destination"] = $data[0]["features"]["callForwardNoReply"]["destination"];
                }
            }

            if ($found) {
                $response = $this->resourceService->updateResource($_ENV['RESOURCES'], $resources);
                if ($response) {
                    http_response_code(200);
                    return json_encode([
                        'message' => 'Successfully updated contact information'
                    ]);
                }
            }


            http_response_code(404);
            return json_encode([
                'message' => 'Failed to update contact information'
            ]);
        }

        return null;
    }
}