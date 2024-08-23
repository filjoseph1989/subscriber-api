<?php

namespace Controllers;

use Interfaces\ValidationServiceInterface;
use Models\Database;
use Models\SubscriberModel;
use PDOException;

class SubscriberController
{
    private $model;
    private $subscriberModel;
    private $validator;

    public function __construct(
        ValidationServiceInterface $validator
    ) {
        $this->validator = $validator;
    }

    public function getSubscriber(string $phoneNumber)
    {
        $this->lazyLoadModel();

        $subscriber = $this->subscriberModel->getSubscriberByPhoneNumber($phoneNumber);
        if ($subscriber) {
            if (isset($subscriber['password'])) {
                $subscriber['password'] = ''; // Mask the password
            }
            $subscriber["features"] = json_decode($subscriber["features"], true);
            http_response_code(200);
            echo json_encode($subscriber);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Contact not found']);
        }
    }

    public function addSubscriber(array $newSubscriber = null)
    {
        $newSubscriber = $newSubscriber ?? $this->getRequestedData();

        if (!$this->validator->validate($newSubscriber)) {
            http_response_code($this->validator->getResponseStatus());
            echo $this->validator->getResponse();
            return;
        }

        try {
            $this->lazyLoadModel();

            $id = $this->subscriberModel->addSubscriber($newSubscriber);
            http_response_code(201); // Created
            echo json_encode([
                'message' => "Subscriber added ID: {$id}, Phone Number: {$newSubscriber['phoneNumber']}",
                "id" => $id,
                "phoneNumber" => $newSubscriber['phoneNumber']
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to add subscriber']);
        }
    }

    public function updateSubscriber(array $updatedSubscriber = null)
    {
        $updatedSubscriber = $updatedSubscriber ?? $this->getRequestedData();

        if (!$this->validator->validate($updatedSubscriber)) {
            http_response_code($this->validator->getResponseStatus());
            echo $this->validator->getResponse();
            return;
        }

        try {
            $this->lazyLoadModel();

            if (!$this->subscriberModel->phoneNumberExists($updatedSubscriber['phoneNumber'])) {
                http_response_code(404);
                echo json_encode(['message' => 'Subscriber not found']);
                return;
            }

            $updated = $this->subscriberModel->updateSubscriber($updatedSubscriber);
            if ($updated) {
                http_response_code(200);
                echo json_encode(['message' => 'Subscriber updated']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Subscriber not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update subscriber']);
        }
    }

    public function deleteSubscriber(string $phoneNumber)
    {
        try {
            $this->lazyLoadModel();

            if (!$this->subscriberModel->phoneNumberExists($phoneNumber)) {
                http_response_code(404);
                echo json_encode(['message' => 'Subscriber not found']);
                return;
            }
            $deleted = $this->subscriberModel->deleteSubscriber($phoneNumber);
            if ($deleted) {
                http_response_code(200);
                echo json_encode(['message' => "Subscriber {$phoneNumber} deleted"]);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Subscriber not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete subscriber']);
        }
    }

    public function getAllSubscribers(int $limit = 100, int $offset = 0)
    {
        try {
            $this->lazyLoadModel();

            if ($limit > 100) {
                $this->subscriberModel->setLimit($limit);
            }
            if ($offset > 0) {
                if ($offset > $limit) {
                    $this->subscriberModel->setOffset($offset);
                } else {
                    $this->subscriberModel->setOffset(0);
                }
            }

            $subscribers = $this->subscriberModel->getAllSubscribers();

            // Calculate the next and previous offsets
            $nextOffset = $offset + $limit;
            $prevOffset = $offset - $limit;

            // Ensure previous offset doesn't go below 0
            $prevOffset = $prevOffset < 0 ? 0 : $prevOffset;

            foreach ($subscribers as &$resource) {
                $resource['password'] = ''; // Masking the password
                $resource["features"] = isset($resource["features"]) ? json_decode($resource["features"], true) : [];

                // Build the pagination links
                $resource["links"] = [
                    'prev' => "/ims/subscriber/all/{$limit}/{$prevOffset}",
                    'next' => "/ims/subscriber/all/{$limit}/{$nextOffset}"
                ];
            }

            http_response_code(200);
            echo json_encode($subscribers);
        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve subscribers']);
        }
    }

    public function setTestSubscriberModel($model)
    {
        $this->subscriberModel = $model;
    }

    public function getRequestedData()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * This strategy allows loading modal on demand.
     * Todo: There will be better implementation of this strategy.
     * @return void
     */
    private function lazyLoadModel()
    {
        if ($this->subscriberModel == null) {
            $this->subscriberModel = new SubscriberModel(new Database());
        }
    }
}