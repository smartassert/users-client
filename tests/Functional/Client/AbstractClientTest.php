<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\Factory\ApiKeyCollectionFactory;
use SmartAssert\UsersClient\Factory\ApiKeyFactory;
use SmartAssert\UsersClient\Factory\ObjectFactory;
use SmartAssert\UsersClient\Factory\RefreshableTokenFactory;
use SmartAssert\UsersClient\Factory\TokenFactory;
use SmartAssert\UsersClient\Factory\UserFactory;
use SmartAssert\UsersClient\RequestBuilder;
use SmartAssert\UsersClient\Routes;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;

abstract class AbstractClientTest extends TestCase
{
    protected MockHandler $mockHandler;
    protected Client $client;
    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();

        $httpFactory = new HttpFactory();

        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->httpHistoryContainer = new HttpHistoryContainer();
        $handlerStack->push(Middleware::history($this->httpHistoryContainer));

        $arrayAccessor = new ArrayAccessor();

        $this->client = new Client(
            $httpFactory,
            $httpFactory,
            new RequestBuilder(),
            new HttpClient([
                'handler' => $handlerStack,
            ]),
            new Routes(
                'https://users.example.com',
            ),
            new ObjectFactory(
                new ApiKeyCollectionFactory(
                    new ApiKeyFactory($arrayAccessor),
                ),
                new RefreshableTokenFactory(),
                new TokenFactory($arrayAccessor),
                new UserFactory($arrayAccessor),
            ),
        );
    }

    protected function getLastRequest(): RequestInterface
    {
        $request = $this->httpHistoryContainer->getTransactions()->getRequests()->getLast();
        \assert($request instanceof RequestInterface);

        return $request;
    }
}
