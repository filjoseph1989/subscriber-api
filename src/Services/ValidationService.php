<?php

namespace Services;

class ValidationService
{
    private string $response;
    private int $responseStatus;

    /**
     * Validate the input data for a new subscriber
     * @param mixed $data
     * @return bool
     */
    public function validateSubscriberData($data)
    {
        // Validate the input data
        if (!isset($data["phoneNumber"])) {
            $this->responseStatus = 400;
            $this->response = json_encode(['message' => 'Phone number is required']);
            return false;
        }
        if (!isset($data["username"])) {
            $this->responseStatus = 400;
            $this->response = json_encode(['message' => 'Username is required']);
            return false;
        }
        if (!isset($data["password"])) {
            $this->responseStatus =400;
            $this->response = json_encode(['message' => 'Password is required']);
            return false;
        }
        if (isset($data["domain"])) {
            if (!filter_var($data["domain"], FILTER_VALIDATE_DOMAIN)) {
                $this->responseStatus =400;
                $this->response = json_encode(['message' => 'Invalid domain']);
                return false;
            }
        }
        if (isset($data["status"])) {
            $allowedStatuses = ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'DISABLED', 'DELETED', 'UNKNOWN'];
            if (!in_array($data["status"], $allowedStatuses)) {
                $this->responseStatus = 400;
                $this->response = json_encode(['message' => 'Invalid status']);
                return false;
            }
        }
        if (isset($data["features"]["callForwardNoReply"])) {
            if (!empty($data["features"]["callForwardNoReply"])) {
                if (isset($data["features"]["callForwardNoReply"]["provisioned"])) {
                    if (!is_bool($data["features"]["callForwardNoReply"]["provisioned"])) {
                        $this->responseStatus = 400;
                        $this->response = json_encode(['message' => 'Invalid provisioned value']);
                        return false;
                    }
                }
                if (isset($data["features"]["callForwardNoReply"]["destination"])) {
                    if (!filter_var($data["features"]["callForwardNoReply"]["destination"], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^tel:\+\d+$/']])) {
                        $this->responseStatus = 400;
                        $this->response = json_encode(['message' => 'Invalid destination phone number']);
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Return the generated response
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return response status code
     * @return int
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }
}
