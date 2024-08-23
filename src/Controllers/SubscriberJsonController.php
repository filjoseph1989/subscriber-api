<?php

namespace Controllers;

class SubscriberJsonController
{
    public function getSubscriber(string $phoneNumber)
    {
        $resources = file_get_contents($_ENV['RESOURCES']);
        $resources = json_decode($resources, true);

        foreach ($resources as $resource) {
            if ($resource['phoneNumber'] == $phoneNumber) {
                if (isset($resource['password'])) {
                    $resource['password'] = '';
                }
                http_response_code(200);
                echo json_encode($resource);
                return;
            }
        }

        http_response_code(404);
        echo json_encode([
            'message' => 'Contact not found'
        ]);
    }

    public function addSubscriber()
    {
        $newSubscriber = json_decode(file_get_contents('php://input'), true);
        $resources = $this->getAllResources();

        if (!isset($newSubscriber['phoneNumber'], $newSubscriber['username'], $newSubscriber['password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid data']);
            return;
        }

        // Check if subscriber already exists
        foreach ($resources as $resource) {
            if ($resource['phoneNumber'] === $newSubscriber['phoneNumber']) {
                http_response_code(409); // Conflict
                echo json_encode(['message' => 'Subscriber already exists']);
                return;
            }
        }

        // Add new subscriber
        $resources[] = $newSubscriber;
        $this->saveResources($resources);

        http_response_code(201);
        echo json_encode(['message' => 'Subscriber added']);
    }

    public function updateSubscriber()
    {
        $updatedSubscriber = json_decode(file_get_contents('php://input'), true);
        $resources = $this->getAllResources();

        foreach ($resources as &$resource) {
            if ($resource['phoneNumber'] === $updatedSubscriber['phoneNumber']) {
                // Update subscriber data
                $resource = array_merge($resource, $updatedSubscriber);
                $this->saveResources($resources);

                http_response_code(200);
                echo json_encode(['message' => 'Subscriber updated']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Subscriber not found']);
    }

    public function deleteSubscriber(string $phoneNumber)
    {
        $resources = $this->getAllResources();

        foreach ($resources as $key => $resource) {
            if ($resource['phoneNumber'] === $phoneNumber) {
                unset($resources[$key]);
                $this->saveResources($resources);

                http_response_code(200);
                echo json_encode(['message' => 'Subscriber deleted']);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Subscriber not found']);
    }

    private function getAllResources()
    {
        $resources = file_get_contents($_ENV['RESOURCES']);
        return json_decode($resources, true);
    }

    private function saveResources(array $resources)
    {
        file_put_contents($_ENV['RESOURCES'], json_encode(array_values($resources), JSON_PRETTY_PRINT));
    }
}
