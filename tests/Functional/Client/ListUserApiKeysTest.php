<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\GetJwtTokenTrait;
use SmartAssert\UsersClient\Tests\Functional\GetUserIdTrait;
use webignition\HttpHistoryContainer\Container as HttpHistoryContainer;

class ListUserApiKeysTest extends AbstractClientTest
{
    use GetJwtTokenTrait;
    use GetUserIdTrait;
    use NetworkErrorExceptionDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;

    private HttpHistoryContainer $httpHistoryContainer;

    protected function setUp(): void
    {
        $this->httpHistoryContainer = new HttpHistoryContainer();

        parent::setUp();
    }

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateUserThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->listUserApiKeys('token');
    }

    /**
     * @dataProvider listApiKeysSuccessDataProvider
     */
    public function testListApiKeySuccess(
        string $token,
        ResponseInterface $httpFixture,
        ApiKeyCollection $expected,
        string $expectedAuthorizationHeader
    ): void {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->listUserApiKeys($token);
        self::assertEquals($expected, $actual);

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
    public function listApiKeysSuccessDataProvider(): array
    {
        $token = $this->getJwtToken();
        $expectedAuthorizationHeader = 'Bearer ' . $token;

        return [
            'single' => [
                'userToken' => $token,
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        [
                            'label' => null,
                            'key' => 'key1',
                        ],
                    ])
                ),
                'expected' => new ApiKeyCollection([
                    new ApiKey(null, 'key1'),
                ]),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
            ],
            'multiple' => [
                'userToken' => $token,
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        [
                            'label' => null,
                            'key' => 'key2',
                        ],
                        [
                            'label' => 'user defined label 1',
                            'key' => 'key3',
                        ],
                        [
                            'label' => 'user defined label 2',
                            'key' => 'key4',
                        ],
                    ])
                ),
                'expected' => new ApiKeyCollection([
                    new ApiKey(null, 'key2'),
                    new ApiKey('user defined label 1', 'key3'),
                    new ApiKey('user defined label 2', 'key4'),
                ]),
                'expectedAuthorizationHeader' => $expectedAuthorizationHeader,
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
