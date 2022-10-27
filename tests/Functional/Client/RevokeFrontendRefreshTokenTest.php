<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;

class RevokeFrontendRefreshTokenTest extends AbstractClientTest
{
    use CommonNonSuccessResponseDataProviderTrait;

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testCreateFrontendTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->revokeFrontendRefreshToken('admin token', 'user id');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testVerifyFrontendToken(): void
    {
        $adminToken = 'admin token value';
        $userId = md5((string) rand());

        $this->mockHandler->append(new Response());

        $this->client->revokeFrontendRefreshToken($adminToken, $userId);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('content-type'));
        self::assertSame($adminToken, $request->getHeaderLine('authorization'));
        self::assertSame(http_build_query(['id' => $userId]), $request->getBody()->getContents());
    }
}
