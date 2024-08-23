<?php

namespace Controllers;
use Models\Database;
use Models\SubscriberModel;
use PDOException;
use Services\ValidationService;

class SubscriberController
{
    private $model;
    private $subscriberModel;
    private $validator;

    public function __construct()
    {
        // $database = new Database();
        // $this->model = new SubscriberModel($database);
        $this->validator = new ValidationService();
    }

    public function setTestSubscriberModel($model)
    {
        $this->subscriberModel = $model;
    }

    public function getSubscriber(string $phoneNumber)
    {
        if ($this->subscriberModel == null) {
            $this->subscriberModel = new SubscriberModel(new Database());
        }
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

    public function addSubscriber()
    {
        $newSubscriber = json_decode(file_get_contents('php://input'), true);

        if (!$this->validator->validateSubscriberData($newSubscriber)) {
            http_response_code($this->validator->getResponseStatus());
            echo $this->validator->getResponse();
        }

        try {
            $id = $this->model->addSubscriber($newSubscriber);
            http_response_code(201); // Created
            echo json_encode(['message' => 'Subscriber added']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to add subscriber']);
        }
    }

    public function updateSubscriber()
    {
        $updatedSubscriber = json_decode(file_get_contents('php://input'), true);

        if (!$this->validator->validateSubscriberData($updatedSubscriber)) {
            http_response_code($this->validator->getResponseStatus());
            echo $this->validator->getResponse();
        }

        try {
            $updated = $this->model->updateSubscriber($updatedSubscriber);
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
            $deleted = $this->model->deleteSubscriber($phoneNumber);
            if ($deleted) {
                http_response_code(200);
                echo json_encode(['message' => 'Subscriber deleted']);
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
            if ($limit > 100) {
                $this->model->setLimit($limit);
            }
            if ($offset > 0) {
                $this->model->setOffset($offset);
            }
            $subscribers = $this->model->getAllSubscribers();

            foreach ($subscribers as &$resource) {
                $resource['password'] = ''; // Masking the password
                $resource["features"] = isset($resource["features"]) ? json_decode($resource["features"], true) : [];
            }

            http_response_code(200);
            echo json_encode($subscribers);
        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to retrieve subscribers'] . $th->getMessage());
        }
    }
}