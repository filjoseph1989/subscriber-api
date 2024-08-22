<?php

namespace Factory;

use Api\ApiRequest;
use Services\ResourceService;
use Services\ValidationService;

class ApiRequestFactory
{
    public static function create(): ApiRequest
    {
        $resourceService = new ResourceService();
        $validationService = new ValidationService();

        return new ApiRequest($resourceService, $validationService);
    }
}