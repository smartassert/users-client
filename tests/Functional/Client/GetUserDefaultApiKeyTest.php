<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class GetUserDefaultApiKeyTest extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testGetUserDefaultApiKeyThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->getUserDefaultApiKey('token');
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testGetUserDefaultApiKeyThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->getUserDefaultApiKey('token');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testGetUserDefaultApiKeySuccess(): void
    {
        $httpFixture = new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'label' => null,
                'key' => 'key1',
            ])
        );

        $token = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->getUserDefaultApiKey($token);
        self::assertEquals(new ApiKey(null, 'key1'), $actual);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }
}
