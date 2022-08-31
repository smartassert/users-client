<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\RequestBuilder;
use SmartAssert\UsersClient\Routes;

abstract class AbstractClientTest extends TestCase
{
    protected MockHandler $mockHandler;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $httpFactory = new HttpFactory();

        $this->client = new Client(
            $httpFactory,
            $httpFactory,
            new RequestBuilder(),
            new HttpClient([
                'handler' => $this->createHandlerStack(),
            ]),
            new Routes(
                'https://users.example.com',
            )
        );
    }

    protected function createHandlerStack(): HandlerStack
    {
        return HandlerStack::create($this->mockHandler);
    }
}
