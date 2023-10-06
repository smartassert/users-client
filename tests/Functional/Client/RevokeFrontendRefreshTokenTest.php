<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;

class RevokeFrontendRefreshTokenTest extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testRevokeThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->revokeFrontendRefreshToken('token', 'refresh token');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testRevokeRequestProperties(): void
    {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        $this->mockHandler->append(new Response());

        $this->client->revokeFrontendRefreshToken($token, $refreshToken);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('content-type'));
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
        self::assertSame(http_build_query(['refresh_token' => $refreshToken]), $request->getBody()->getContents());
    }
}
