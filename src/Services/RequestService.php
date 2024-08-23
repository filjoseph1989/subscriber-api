<?php

namespace Services;

use Interfaces\RequestServiceInterface;

class RequestService implements RequestServiceInterface
{
    public function getRequestData(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}