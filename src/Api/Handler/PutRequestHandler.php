<?php

namespace Api\Handler;

use Api\Contracts\RequestHandlerInterface;
use Services\ResourceService;
use Services\ValidationService;

class PutRequestHandler implements RequestHandlerInterface
{
    private $resourceService;
    private $validationService;

    public function __construct(ResourceService $resourceService, ValidationService $validationService)
    {
        $this->resourceService = $resourceService;
        $this->validationService = $validationService;
    }

    public function handle(array $uriSegments): ?string
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
