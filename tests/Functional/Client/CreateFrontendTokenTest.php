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

class CreateFrontendTokenTest extends AbstractClientTestCase
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
    public function testCreateFrontendTokenThrowsException(
        ClientExceptionInterface|ResponseInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->createFrontendToken('email', 'password');
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testCreateFrontendTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->createFrontendToken('email', 'password');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testCreateFrontendTokenInvalidResponseData(): void
    {
        $this->doInvalidResponseDataTest(
            function () {
                $this->client->createFrontendToken('email', 'password');
            },
            RefreshableToken::class
        );
    }

    /**
     * @dataProvider createFrontendTokenSuccessDataProvider
     */
    public function testCreateFrontendTokenSuccess(ResponseInterface $httpFixture, RefreshableToken $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $email = 'email value';
        $password = 'password value';

        $actual = $this->client->createFrontendToken($email, $password);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('content-type'));
        self::assertSame(
            json_encode(['username' => $email, 'password' => $password]),
            $request->getBody()->getContents()
        );
    }

    /**
     * @return array<mixed>
     */
    public function createFrontendTokenSuccessDataProvider(): array
    {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        return [
            'created' => [
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
