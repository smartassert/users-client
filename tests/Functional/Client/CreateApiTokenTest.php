<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\ApiToken;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\ValidJsonResponseDataProviderTrait;

class CreateApiTokenTest extends AbstractClientTest
{
    use NetworkErrorExceptionDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use ValidJsonResponseDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateApiTokenThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->createApiToken('api key');
    }

    /**
     * @dataProvider createApiTokenSuccessDataProvider
     */
    public function testCreateApiTokenSuccess(ResponseInterface $httpFixture, ApiToken $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $apiKey = 'api key value';

        $actual = $this->client->createApiToken($apiKey);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame($apiKey, $request->getHeaderLine('authorization'));
    }

    /**
     * @return array<mixed>
     */
    public function createApiTokenSuccessDataProvider(): array
    {
        $token = md5((string) rand());

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'token' => $token,
                    ])
                ),
                'expected' => new ApiToken($token),
            ],
        ];
    }
}