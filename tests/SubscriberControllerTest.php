<?php

namespace Tests\Controllers;

use Controllers\SubscriberController;
use Models\Database;
use Models\SubscriberModel;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use Dotenv\Dotenv;
use Services\RequestService;
use Services\ValidationService;

class SubscriberControllerTest extends TestCase
{
    private $subscriberController;
    private $subscriberModel;
    private $databaseMock;
    private $subscriberControllerMock;
    private $validatorMock;
    private $requestMock;

    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }

        // Mock the Database class
        $this->databaseMock = $this->createMock(Database::class);
        $pdoMock = $this->createMock(PDO::class);
        $this->databaseMock->method('getConnection')->willReturn($pdoMock);
        $this->validatorMock = $this->createMock(ValidationService::class);
        $this->requestMock = $this->createMock(RequestService::class);

        // Mock the SubscriberModel class
        $this->subscriberModel = $this->createMock(SubscriberModel::class);
        $this->subscriberController = new SubscriberController(
            $this->validatorMock,
            $this->requestMock
        );
        $this->subscriberController->setTestSubscriberModel($this->subscriberModel);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->subscriberController = null;
        $this->subscriberModel = null;
        $this->databaseMock = null;
        stream_wrapper_restore('php');
    }

    public function testGetSubscriberSuccess()
    {
        $phoneNumber = '1234567890';
        $subscriberData = [
            'phoneNumber' => $phoneNumber,
            'username' => 'testuser',
            'password' => 'testpassword',
            'domain' => 'testdomain',
            'status' => 'active',
            'features' => json_encode(['feature1' => true, 'feature2' => false]),
        ];

        // Mock the getSubscriberByPhoneNumber method
        $this->subscriberModel->expects($this->once())
            ->method('getSubscriberByPhoneNumber')
            ->with($phoneNumber)
            ->willReturn($subscriberData);

        ob_start();
        $this->subscriberController->getSubscriber($phoneNumber);
        $output = ob_get_clean();

        $expectedOutput = json_encode([
            'phoneNumber' => $phoneNumber,
            'username' => 'testuser',
            'password' => '', // Password masked
            'domain' => 'testdomain',
            'status' => 'active',
            'features' => ['feature1' => true, 'feature2' => false],
        ]);

        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
        $this->assertTrue(true);
    }

    public function testGetSubscriberNotFound()
    {
        $phoneNumber = '1234567890';

        // Mock the getSubscriberByPhoneNumber method to return null
        $this->subscriberModel->expects($this->once())
            ->method('getSubscriberByPhoneNumber')
            ->with($phoneNumber)
            ->willReturn(null);

        ob_start(); // Capture output
        $this->subscriberController->getSubscriber($phoneNumber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Contact not found']);

        $this->assertEquals(404, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testAddSubscriberSuccess()
    {
        $newSubscriber = [
            'phoneNumber' => '1234567890',
            'username' => 'testuser',
            'password' => 'testpassword',
            'domain' => 'example.com',
            'status' => 'ACTIVE',
            'features' => ['feature1' => true, 'feature2' => false],
        ];

        $mockId = 123;

        // Mock the addSubscriber method
        $this->subscriberModel->expects($this->once())
            ->method('addSubscriber')
            ->with($newSubscriber)
            ->willReturn($mockId);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($newSubscriber)
            ->willReturn(true);

        ob_start(); // Capture output
        $this->subscriberController->addSubscriber($newSubscriber);
        $output = ob_get_clean();

        $expectedOutput = json_encode([
            'id' => $mockId,
            'message' => "Subscriber added",
            'phoneNumber' => $newSubscriber['phoneNumber'],
        ]);

        $this->assertEquals(201, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testAddSubscriberFailure()
    {
        $newSubscriber = [
            'phoneNumber' => '1234567890',
            'username' => 'testuser',
            'password' => 'testpassword',
            'domain' => 'example.com',
            'status' => 'ACTIVE',
            'features' => ['feature1' => true, 'feature2' => false],
        ];

        // Mock the addSubscriber method to throw an exception
        $this->subscriberModel->expects($this->once())
            ->method('addSubscriber')
            ->with($newSubscriber)
            ->willThrowException(new PDOException('Failed to add subscriber'));

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($newSubscriber)
            ->willReturn(true);

        ob_start(); // Capture output
        $this->subscriberController->addSubscriber($newSubscriber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Failed to add subscriber']);

        $this->assertEquals(500, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testUpdateSubscriberSuccess()
    {
        $updatedSubscriber = [
            'phoneNumber' => '1234567890',
            'username' => 'updateduser',
            'password' => 'updatedpassword',
            'domain' => 'updateddomain.com',
            'status' => 'INACTIVE',
            'features' => ['feature3' => true, 'feature4' => false],
        ];

        // Mock the phoneNumberExists and updateSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($updatedSubscriber['phoneNumber'])
            ->willReturn(true);

        $this->subscriberModel->expects($this->once())
            ->method('updateSubscriber')
            ->with($updatedSubscriber)
            ->willReturn(true);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($updatedSubscriber)
            ->willReturn(true);

        ob_start(); // Capture output
        $this->subscriberController->updateSubscriber($updatedSubscriber);
        $output = ob_get_clean();

        $expectedOutput = json_encode([
            'id' => null,
            'message' => 'Subscriber updated',
            'phoneNumber' => null
        ]);

        $this->assertEquals(201, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testUpdateSubscriberNotFound()
    {
        $updatedSubscriber = [
            'phoneNumber' => '1234567890',
            'username' => 'updateduser',
            'password' => 'updatedpassword',
            'domain' => 'updateddomain.com',
            'status' => 'INACTIVE',
            'features' => ['feature3' => true, 'feature4' => false],
        ];

        // Mock the phoneNumberExists and updateSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($updatedSubscriber['phoneNumber'])
            ->willReturn(false);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($updatedSubscriber)
            ->willReturn(true);

        $this->subscriberModel->expects($this->never())->method('updateSubscriber');

        ob_start(); // Capture output
        $this->subscriberController->updateSubscriber($updatedSubscriber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Subscriber not found']);

        $this->assertEquals(404, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testUpdateSubscriberFailure()
    {
        $updatedSubscriber = [
            'phoneNumber' => '1234567890',
            'username' => 'updateduser',
            'password' => 'updatedpassword',
            'domain' => 'updateddomain.com',
            'status' => 'INACTIVE',
            'features' => ['feature3' => true, 'feature4' => false],
        ];

        // Mock the phoneNumberExists and updateSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($updatedSubscriber['phoneNumber'])
            ->willReturn(true);

        $this->subscriberModel->expects($this->once())
            ->method('updateSubscriber')
            ->with($updatedSubscriber)
            ->willReturn(false);

        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($updatedSubscriber)
            ->willReturn(true);

        ob_start(); // Capture output
        $this->subscriberController->updateSubscriber($updatedSubscriber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Subscriber not found']);

        $this->assertEquals(404, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testDeleteSubscriberSuccess()
    {
        $phoneNumber = '1234567890';

        // Mock the phoneNumberExists and deleteSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($phoneNumber)
            ->willReturn(true);

        $this->subscriberModel->expects($this->once())
            ->method('deleteSubscriber')
            ->with($phoneNumber)
            ->willReturn(true);

        ob_start(); // Capture output
        $this->subscriberController->deleteSubscriber($phoneNumber);
        $output = ob_get_clean();

        $expectedOutput = json_encode([
            'id'  => null,
            'message' => "Subscriber {$phoneNumber} deleted",
            'phoneNumber' => null
        ]);

        $this->assertEquals(201, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testDeleteSubscriberNotFound()
    {
        $phoneNumber = '1234567890';

        // Mock the phoneNumberExists and deleteSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($phoneNumber)
            ->willReturn(false);

        $this->subscriberModel->expects($this->never())
            ->method('deleteSubscriber');

        ob_start(); // Capture output
        $this->subscriberController->deleteSubscriber($phoneNumber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Subscriber not found']);

        $this->assertEquals(404, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testDeleteSubscriberFailure()
    {
        $phoneNumber = '1234567890';

        // Mock the phoneNumberExists and deleteSubscriber methods
        $this->subscriberModel->expects($this->once())
            ->method('phoneNumberExists')
            ->with($phoneNumber)
            ->willReturn(true);

        $this->subscriberModel->expects($this->once())
            ->method('deleteSubscriber')
            ->with($phoneNumber)
            ->willReturn(false);

        ob_start(); // Capture output
        $this->subscriberController->deleteSubscriber($phoneNumber);
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Subscriber not found']);

        $this->assertEquals(404, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testGetAllSubscribersSuccess()
    {
        $subscribers = [
            [
                'id' => 3,
                'phone_number' => '16875189451',
                'username' => '97287',
                'password' => 'testpassword1',
                'domain' => 'ims.mnc660.mcc302.3gppnetwork.org',
                'status' => 'INACTIVE',
                'features' => json_encode(['feature1' => true, 'feature2' => false]),
            ],
            [
                'id' => 4,
                'phone_number' => '16875186700',
                'username' => '89922',
                'password' => 'testpassword2',
                'domain' => 'ims.mnc660.mcc302.3gppnetwork.org',
                'status' => 'INACTIVE',
                'features' => json_encode(['feature3' => true, 'feature4' => false]),
            ],
        ];

        $limit = 100;
        $offset = 0;

        // Mock the getAllSubscribers method
        $this->subscriberModel->expects($this->once())
            ->method('getAllSubscribers')
            ->willReturn($subscribers);

        ob_start(); // Capture output
        $this->subscriberController->getAllSubscribers($limit, $offset);
        $output = ob_get_clean();

        $expectedOutput = json_encode([
            'data' => [
                [
                    'id' => 3,
                    'phone_number' => '16875189451',
                    'username' => '97287',
                    'password' => '',
                    'domain' => 'ims.mnc660.mcc302.3gppnetwork.org',
                    'status' => 'INACTIVE',
                    'features' => ['feature1' => true, 'feature2' => false],
                ],
                [
                    'id' => 4,
                    'phone_number' => '16875186700',
                    'username' => '89922',
                    'password' => '',
                    'domain' => 'ims.mnc660.mcc302.3gppnetwork.org',
                    'status' => 'INACTIVE',
                    'features' => ['feature3' => true, 'feature4' => false],
                ],
            ],
            'links' => [
                'prev' => "/ims/subscriber/all/{$limit}/0",
                'next' => "/ims/subscriber/all/{$limit}/{$limit}",
            ],
        ]);

        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }

    public function testGetAllSubscribersFailure()
    {
        // Mock the getAllSubscribers method to throw an exception
        $this->subscriberModel->expects($this->once())
            ->method('getAllSubscribers')
            ->willThrowException(new PDOException('Failed to retrieve subscribers'));

        ob_start(); // Capture output
        $this->subscriberController->getAllSubscribers();
        $output = ob_get_clean();

        $expectedOutput = json_encode(['message' => 'Failed to retrieve subscribers']);

        $this->assertEquals(500, http_response_code());
        $this->assertJsonStringEqualsJsonString($expectedOutput, $output);
    }
}