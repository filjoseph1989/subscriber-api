<?php

namespace Services;

class ResourceService
{
    public function getResources($url)
    {
        $resources = file_get_contents($url);
        return json_decode($resources, true);
    }
}