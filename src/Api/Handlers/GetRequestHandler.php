<?php

namespace Api\Handlers;

use Api\Contracts\RequestHandlerInterface;
use Services\ResourceService;
use Services\ValidationService;

class GetRequestHandler implements RequestHandlerInterface
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
}
