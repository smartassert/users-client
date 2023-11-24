<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RefreshFrontendCredentialsTest extends AbstractClientTestCase
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
    public function testRefreshFrontendTokenThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->refreshFrontendCredentials(md5((string) rand()));
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testRefreshFrontendTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->refreshFrontendCredentials(md5((string) rand()));
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->getHttpResponse());
        }
    }

    public function testRefreshFrontendTokenInvalidResponseData(): void
    {
        $this->doInvalidResponseDataTest(
            function () {
                $this->client->refreshFrontendCredentials(md5((string) rand()));
            },
            FrontendCredentials::class
        );
    }

    /**
     * @dataProvider refreshFrontendTokenSuccessDataProvider
     */
    public function testRefreshFrontendTokenSuccess(ResponseInterface $httpFixture, FrontendCredentials $expected): void
    {
        $refreshToken = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->refreshFrontendCredentials($refreshToken);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('content-type'));
        self::assertSame(
            json_encode(['refresh_token' => $refreshToken]),
            $request->getBody()->getContents()
        );
    }

    /**
     * @return array<mixed>
     */
    public function refreshFrontendTokenSuccessDataProvider(): array
    {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());
        $apiKey = md5((string) rand());

        return [
            'refreshed' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'token' => $token,
                        'refresh_token' => $refreshToken,
                        'api_key' => $apiKey,
                    ])
                ),
                'expected' => new FrontendCredentials($token, $refreshToken, $apiKey),
            ],
        ];
    }
}
