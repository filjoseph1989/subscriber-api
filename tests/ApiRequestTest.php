<?php

namespace Api;

use PHPUnit\Framework\TestCase;
use Services\ResourceService;
use PHPUnit\Framework\MockObject\MockObject;

class ApiRequestTest extends TestCase
{
    /**
     * @var ApiRequest
     */
    private $apiRequest;

    /**
     * @var ResourceService|MockObject
     */
    private $resourceServiceMock;

    protected function setUp(): void
    {
        $this->resourceServiceMock = $this->createMock(ResourceService::class);
        $this->apiRequest = new ApiRequest($this->resourceServiceMock);
    }

    public function testGetSubscriberSuccess(): void
    {
        // Mock the getResources method to return a sample resource
        $this->resourceServiceMock
            ->expects($this->once())
            ->method('getResources')
            ->with('path/to/resource.json')
            ->willReturn([
                ['phoneNumber' => '1234567890', 'name' => 'John Doe'],
                ['phoneNumber' => '0987654321', 'name' => 'Jane Doe'],
            ]);

        // Simulate the request environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/1234567890';
        $_ENV['RESOURCES'] = 'path/to/resource.json';

        // Capture the output
        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        // Assert the expected response
        $expectedResponse = json_encode(['phoneNumber' => '1234567890', 'name' => 'John Doe']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }

    public function testGetSubscriberNotFound(): void
    {
        // Mock the getResources method to return a sample resource
        $this->resourceServiceMock
            ->expects($this->once())
            ->method('getResources')
            ->with('path/to/resource.json')
            ->willReturn([
                ['phoneNumber' => '0987654321', 'name' => 'Jane Doe'],
            ]);

        // Simulate the request environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/1234567890';
        $_ENV['RESOURCES'] = 'path/to/resource.json';

        // Capture the output
        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        // Assert the expected response
        $expectedResponse = json_encode(['message' => 'Contact not found']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }

    public function testInvalidMethod(): void
    {
        // Simulate the request environment
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/1234567890';

        // Capture the output
        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        // Assert the expected response
        $expectedResponse = json_encode(['message' => 'Method not allowed']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }

    public function testInvalidRoute(): void
    {
        // Simulate the request environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/invalid/route';

        // Capture the output
        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        // Assert the expected response
        $expectedResponse = json_encode(['message' => 'Route not found']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }
}
