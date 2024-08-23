<?php

namespace Models;

class SubscriberModel
{
    private $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function getSubscriberByPhoneNumber(string $phoneNumber)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subscribers WHERE phone_number = :phone_number");
        $stmt->execute(['phone_number' => $phoneNumber]);
        return $stmt->fetch();
    }

    public function addSubscriber(array $data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO subscribers (phone_number, username, password, domain, status, features) VALUES (:phone_number, :username, :password, :domain, :status, :features)");
        $stmt->execute([
            'phone_number' => $data['phoneNumber'],
            'username' => $data['username'],
            'password' => $data['password'],
            'domain' => $data['domain'],
            'status' => $data['status'],
            'features' => json_encode($data['features'])
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateSubscriber(array $data)
    {
        $stmt = $this->pdo->prepare("UPDATE subscribers SET username = :username, password = :password, domain = :domain, status = :status, features = :features WHERE phone_number = :phone_number");
        return $stmt->execute([
            'phone_number' => $data['phoneNumber'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'domain' => $data['domain'],
            'status' => $data['status'],
            'features' => json_encode($data['features'])
        ]);
    }

    public function deleteSubscriber(string $phoneNumber)
    {
        $stmt = $this->pdo->prepare("DELETE FROM subscribers WHERE phone_number = :phone_number");
        return $stmt->execute(['phone_number' => $phoneNumber]);
    }
}
