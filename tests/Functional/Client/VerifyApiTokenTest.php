<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Tests\Functional\GetJwtTokenTrait;
use SmartAssert\UsersClient\Tests\Functional\GetUserIdTrait;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;

class VerifyApiTokenTest extends AbstractClientTest
{
    use GetJwtTokenTrait;
    use GetUserIdTrait;

    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        $this->httpHistoryContainer = new HttpHistoryContainer();

        parent::setUp();
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
        $token = $this->getJwtToken();
        $userId = $this->getUserId();

        $expectedAuthorizationHeader = 'Bearer ' . $token;

        return [
            'unverified, HTTP 401' => [
                'userToken' => $token,
                'userServiceResponse' => new Response(401),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => null,
            ],
            'unverified, HTTP 500' => [
                'userToken' => $token,
                'userServiceResponse' => new Response(500),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => null,
            ],
            'verified' => [
                'userToken' => $token,
                'userServiceResponse' => new Response(200, [], $userId),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
                'expectedReturnValue' => $userId,
            ],
        ];
    }

    protected function createHandlerStack(): HandlerStack
    {
        $handlerStack = parent::createHandlerStack();
        $handlerStack
            ->push(Middleware::history($this->httpHistoryContainer))
        ;

        return $handlerStack;
    }
}
