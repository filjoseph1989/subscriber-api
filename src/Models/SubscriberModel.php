<?php

namespace Models;

use PDO;

class SubscriberModel
{
    private $pdo;
    private int $limit = 100;
    private int $offset = 0;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    public function getAllSubscribers(): array
    {
        $sql = "SELECT * FROM subscribers LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $this->limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $this->offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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

    public function phoneNumberExists(int $phoneNumber)
    {
        $stmt = $this->pdo->prepare("Select count(*) from subscribers where phone_number = :phone_number");
        $stmt->execute(['phone_number' => $phoneNumber]);
        return $stmt->fetchColumn() > 0;
    }
}
