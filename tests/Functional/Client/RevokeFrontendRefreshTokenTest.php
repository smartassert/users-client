<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;

class RevokeFrontendRefreshTokenTest extends AbstractClientTest
{
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
