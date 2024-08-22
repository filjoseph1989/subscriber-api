<?php

namespace Factory;

use Api\Handler\DeleteRequestHandler;
use Api\Handler\GetRequestHandler;
use Api\Handler\PostRequestHandler;
use Api\Handler\PutRequestHandler;
use Services\ResourceService;
use Services\ValidationService;

class RequestHandleFactory
{
    private $resourceService;
    private $validationService;

    public function __construct(ResourceService $resourceService, ValidationService $validationService)
    {
        $this->resourceService = $resourceService;
        $this->validationService = $validationService;
    }

    public function getHandler($method)
    {
        $handler = null;

        switch ($method) {
            case 'POST':
                $handler = new PostRequestHandler($this->resourceService, $this->validationService);
                break;

            case 'GET':
                $handler = new GetRequestHandler($this->resourceService, $this->validationService);
                break;

            case 'DELETE':
                $handler = new DeleteRequestHandler($this->resourceService, $this->validationService);
                break;

            case 'PUT':
                $handler = new PutRequestHandler($this->resourceService, $this->validationService);
                break;
        }

        return $handler;
    }
}
