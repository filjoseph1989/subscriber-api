<?php

namespace Api\Handler;

use Api\Contracts\RequestHandlerInterface;
use Services\ResourceService;
use Services\ValidationService;

class DeleteRequestHandler implements RequestHandlerInterface
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
}
