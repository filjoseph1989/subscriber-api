<?php

use Api\ApiRequest;
use Api\Contracts\RequestHandlerInterface;
use Api\Handlers\GetRequestHandler;
use Factory\ApiRequestFactory;
use Factory\RequestHandleFactory;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Services\ResourceService;
use Services\ValidationService;

class ApiRequestTest extends TestCase
{
    private $validationServiceMock;
    private $requestHandleFactoryMock;
    private $requestHandlerMock;
    private $apiRequest;

    protected function setUp(): void
    {
        $this->requestHandleFactoryMock = $this->createMock(RequestHandleFactory::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $this->apiRequest = new ApiRequest($this->requestHandleFactoryMock);
    }

    protected function tearDown(): void
    {
        // tear down code here
    }

    public function testGetSubscriberSuccess(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/1234567890';

        $expectedResponse = json_encode([
            'phoneNumber' => '1234567890',
            'name' => 'John Doe'
        ]);

        $this->requestHandleFactoryMock
            ->expects($this->once())
            ->method('getHandler')
            ->with('GET')
            ->willReturn($this->requestHandlerMock);

        $this->requestHandlerMock
            ->expects($this->once())
            ->method('handle')
            ->with(['ims', 'subscriber', '1234567890'])
            ->willReturn($expectedResponse);

        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }

    public function testMethodNotAllowed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/1234567890';

        $this->requestHandleFactoryMock
            ->expects($this->once())
            ->method('getHandler')
            ->with('PATCH')
            ->willReturn(null);

        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        $expectedResponse = json_encode(['message' => 'Method not allowed']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }

    public function testMethodNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/ims/subscriber/';

        $this->requestHandleFactoryMock
            ->expects($this->once())
            ->method('getHandler')
            ->with('GET')
            ->willReturn($this->requestHandlerMock);

        ob_start();
        $this->apiRequest->handleRequest();
        $output = ob_get_clean();

        $expectedResponse = json_encode(['message' => 'Route not found']);
        $this->assertJsonStringEqualsJsonString($expectedResponse, $output);
    }
}
