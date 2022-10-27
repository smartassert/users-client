<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RefreshFrontendTokenTest extends AbstractClientTest
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
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->refreshFrontendToken(new RefreshableToken(md5((string) rand()), md5((string) rand())));
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testRefreshFrontendTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->refreshFrontendToken(new RefreshableToken(md5((string) rand()), md5((string) rand())));
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testRefreshFrontendTokenInvalidResponseData(): void
    {
        $this->doInvalidResponseDataTest(
            function () {
                $this->client->refreshFrontendToken(new RefreshableToken(md5((string) rand()), md5((string) rand())));
            },
            RefreshableToken::class
        );
    }

    /**
     * @dataProvider refreshFrontendTokenSuccessDataProvider
     */
    public function testRefreshFrontendTokenSuccess(ResponseInterface $httpFixture, RefreshableToken $expected): void
    {
        $refreshToken = new RefreshableToken(md5((string) rand()), md5((string) rand()));

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->refreshFrontendToken($refreshToken);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('content-type'));
        self::assertSame(
            json_encode(['refresh_token' => $refreshToken->refreshToken]),
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
                    ])
                ),
                'expected' => new RefreshableToken($token, $refreshToken),
            ],
        ];
    }
}
