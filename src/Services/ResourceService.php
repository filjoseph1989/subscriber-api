<?php

namespace Services;

class ResourceService
{
    /**
     * Return the subcriber resources
     * @param mixed $url
     * @return mixed
     */
    public function getResources($url)
    {
        $resources = file_get_contents($url);
        return json_decode($resources, true);
    }

    /**
     * Updated existing subscriber in resources
     * @param mixed $url
     * @param mixed $resources
     * @return bool
     */
    public function updateResource($url, $resources)
    {
        try {
            $resources = json_encode($resources);
            file_put_contents($url, $resources);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Add new subscriber to resources
     * @param mixed $url
     * @param mixed $resources
     * @return bool
     */
    public function addResource($url, $resources)
    {
        try {
            $resources = json_encode($resources);
            file_put_contents($url, $resources);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}