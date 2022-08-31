<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\RequestBuilder;
use SmartAssert\UsersClient\Routes;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;

class ClientTest extends TestCase
{
    private const USER_TOKEN =
        'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.' .
        'eyJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20iLCJzdWIiOiIwMUZQWkdIQUc2NUUwTjlBUldHNlkxUkgzNCIsImF1ZCI6WyJhcGkiXX0.' .
        'hMGV5MJexFIDIuh5gsqkhJ7C3SDQGnOW7sZVS5b6X08';

    private const USER_ID = '01FPZGHAG65E0N9ARWG6Y1RH34';

    private MockHandler $mockHandler;
    private HttpHistoryContainer $httpHistoryContainer;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $this->httpHistoryContainer = new HttpHistoryContainer();

        $handlerStack = HandlerStack::create($this->mockHandler);
        $handlerStack
            ->push(Middleware::history($this->httpHistoryContainer))
        ;

        $this->client = new Client(
            new HttpFactory(),
            new RequestBuilder(),
            new HttpClient([
                'handler' => $handlerStack,
            ]),
            new Routes(
                'https://users.example.com',
            )
        );
    }

    public function testVerifyApiTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyApiToken('token');
    }

    /**
     * @dataProvider verifyDataProvider
     */
    public function testVerifyApiToken(
        string $userToken,
        ResponseInterface|\Throwable $userServiceResponse,
        string $expectedAuthorizationHeader,
        ?string $expectedReturnValue
    ): void {
        $this->mockHandler->append($userServiceResponse);

        $returnValue = $this->client->verifyApiToken($userToken);
        self::assertSame($expectedReturnValue, $returnValue);

        $sentRequest = $this->httpHistoryContainer->getTransactions()->getRequests()->getLast();
        self::assertInstanceOf(RequestInterface::class, $sentRequest);
        \assert($sentRequest instanceof RequestInterface);

        self::assertSame('GET', $sentRequest->getMethod());

        $authorizationHeader = $sentRequest->getHeaderLine('authorization');
        self::assertSame($expectedAuthorizationHeader, $authorizationHeader);
    }

    /**
     * @return array<mixed>
     */
    public function verifyDataProvider(): array
    {
        $expectedAuthorizationHeader = 'Bearer ' . self::USER_TOKEN;

        return [
            'unverified, HTTP 401' => [
                'userToken' => self::USER_TOKEN,
                'userServiceResponse' => new Response(401),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => null,
            ],
            'unverified, HTTP 500' => [
                'userToken' => self::USER_TOKEN,
                'userServiceResponse' => new Response(500),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => null,
            ],
            'verified' => [
                'userToken' => self::USER_TOKEN,
                'userServiceResponse' => new Response(200, [], self::USER_ID),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => self::USER_ID,
            ],
        ];
    }
}
