<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\User;

class RevokeRefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRevokeUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->revokeFrontendRefreshToken(md5((string) rand()), md5((string) rand()));
    }

    public function testCreateVerifyRefreshFrontendToken(): void
    {
        $token = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($token instanceof RefreshableToken);

        $userFromToken = $this->client->verifyFrontendToken($token->token);
        \assert($userFromToken instanceof User);

        $refreshedToken = $this->client->refreshFrontendToken($token->refreshToken);
        \assert($refreshedToken instanceof RefreshableToken);

        $this->client->revokeFrontendRefreshToken(self::ADMIN_TOKEN, $userFromToken->id);

        self::assertNull($this->client->refreshFrontendToken($refreshedToken->token));
    }
}
