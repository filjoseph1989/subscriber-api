<?php

namespace Api\Contracts;

interface RequestHandlerInterface
{
    public function handle(array $uriSegments);
}