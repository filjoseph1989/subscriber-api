<?php

namespace Api\Handler;

use Api\Contracts\RequestHandlerInterface;
use Services\ResourceService;
use Services\ValidationService;

class PostRequestHandler implements RequestHandlerInterface
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
}
