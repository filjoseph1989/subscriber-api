<?php

namespace Controllers;

use Interfaces\RequestServiceInterface;
use Interfaces\ValidationServiceInterface;
use Models\Database;
use Models\SubscriberModel;
use PDOException;

class SubscriberController
{
    private $model;
    private $subscriberModel;
    private $validator;
    private $request;

    public function __construct(
        ValidationServiceInterface $validator,
        RequestServiceInterface $request,
    ) {
        $this->validator = $validator;
        $this->request = $request;
    }

    /**
     * Use in mocking the model
     * @param mixed $model
     * @return void
     */
    public function setTestSubscriberModel($model)
    {
        $this->subscriberModel = $model;
    }

    public function getSubscriber(string $phoneNumber)
    {
        $this->lazyLoadModel();

        $subscriber = $this->subscriberModel->getSubscriberByPhoneNumber($phoneNumber);

        if (!$subscriber) {
            return $this->respondNotFound('Contact not found');
        }

        $subscriber['password'] = ''; // Mask the password
        $subscriber['features'] = json_decode($subscriber['features'], true);

        return $this->respondSuccess($subscriber);
    }

    public function addSubscriber(array $newSubscriber = null)
    {
        $newSubscriber = $newSubscriber ?? $this->request->getRequestedData();

        if (!$this->validator->validate($newSubscriber)) {
            return $this->respondWithValidationError();
        }

        try {
            $this->lazyLoadModel();
            $id = $this->subscriberModel->addSubscriber($newSubscriber);
            return $this->respondWithSuccess("Subscriber added", $id, $newSubscriber['phoneNumber']);
        } catch (PDOException $e) {
            return $this->respondWithError('Failed to add subscriber');
        }
    }

    public function updateSubscriber(array $updatedSubscriber = null)
    {
        $updatedSubscriber = $updatedSubscriber ?? $this->request->getRequestedData();

        if (!$this->validator->validate($updatedSubscriber)) {
            return $this->respondWithValidationError();
        }

        try {
            $this->lazyLoadModel();

            if (!$this->subscriberModel->phoneNumberExists($updatedSubscriber['phoneNumber'])) {
                return $this->respondWithNotFound('Subscriber not found');
            }

            $updated = $this->subscriberModel->updateSubscriber($updatedSubscriber);

            if ($updated) {
                return $this->respondWithSuccess('Subscriber updated');
            } else {
                return $this->respondWithNotFound('Subscriber not found');
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
                return $this->respondWithNotFound('Subscriber not found');
            }

            $deleted = $this->subscriberModel->deleteSubscriber($phoneNumber);

            if ($deleted) {
                return $this->respondWithSuccess("Subscriber {$phoneNumber} deleted");
            } else {
                return $this->respondWithNotFound('Subscriber not found');
            }
        } catch (PDOException $e) {
            return $this->respondWithError('Failed to delete subscriber');
        }
    }

    public function getAllSubscribers(int $limit = 100, int $offset = 0)
    {
        try {
            $this->lazyLoadModel();

            if ($limit > 100) {
                $this->subscriberModel->setLimit($limit);
            }

            // Yes, this can be simplified, but it produce different result
            if ($offset > 0) {
                if ($offset > $limit) {
                    $this->subscriberModel->setOffset($offset);
                } else {
                    $this->subscriberModel->setOffset(0);
                }
            }

            $this->subscriberModel->setLimit($limit);
            $this->subscriberModel->setOffset($offset);

            $subscribers = $this->subscriberModel->getAllSubscribers();

            $data = [];
            foreach ($subscribers as &$subscriber) {
                $this->sanitizeSubscriber($subscriber);
                $data['data'][] = $subscriber;
            }
            $data['links'] = $this->addPaginationLinks($subscriber, $limit, $offset);

            return $this->respondArrayWithSuccess($data);
        } catch (\Throwable $th) {
            return $this->respondWithError('Failed to retrieve subscribers');
        }
    }

    /**
     * This strategy allows loading the model on demand instead of preloading it.
     * Todo: There will be better implementation of this strategy.
     * @return void
     */
    private function lazyLoadModel()
    {
        if ($this->subscriberModel == null) {
            $this->subscriberModel = new SubscriberModel(new Database());
        }
    }

    private function respondNotFound(string $message)
    {
        http_response_code(404);
        echo json_encode(['message' => $message]);
    }

    private function respondSuccess(array $data)
    {
        http_response_code(200);
        echo json_encode($data);
    }

    private function respondWithValidationError()
    {
        http_response_code($this->validator->getResponseStatus());
        echo $this->validator->getResponse();
    }

    private function respondWithSuccess(string $message, int $id = null, string $phoneNumber = null)
    {
        http_response_code(201); // Created
        echo json_encode([
            'id' => $id,
            'message' => $message,
            'phoneNumber' => $phoneNumber,
        ]);
    }

    private function respondArrayWithSuccess(array $data)
    {
        http_response_code(200);
        echo json_encode($data);
    }

    private function respondWithError(string $message)
    {
        http_response_code(500);
        echo json_encode(['message' => $message]);
    }

    private function respondWithNotFound(string $message)
    {
        http_response_code(404);
        echo json_encode(['message' => $message]);
    }

    private function sanitizeSubscriber(array &$subscriber)
    {
        $subscriber['password'] = ''; // Masking the password
        $subscriber['features'] = isset($subscriber['features']) ? json_decode($subscriber['features'], true) : [];
    }

    private function addPaginationLinks(array &$subscriber, int $limit, int $offset)
    {
        $nextOffset = $offset + $limit;
        $prevOffset = max(0, $offset - $limit);

        return [
            'prev' => "/ims/subscriber/all/{$limit}/{$prevOffset}",
            'next' => "/ims/subscriber/all/{$limit}/{$nextOffset}",
        ];
    }
}