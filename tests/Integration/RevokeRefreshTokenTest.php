<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\User;

class RevokeRefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRevokeUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->revokeFrontendRefreshToken(md5((string) rand()), md5((string) rand()));
    }

    public function testRevokeSuccess(): void
    {
        $token = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($token instanceof FrontendCredentials);

        $userFromToken = $this->client->verifyFrontendToken($token->token);
        \assert($userFromToken instanceof User);

        $refreshedToken = $this->client->refreshFrontendCredentials($token->refreshToken);
        \assert($refreshedToken instanceof FrontendCredentials);

        $this->client->revokeFrontendRefreshToken($token->token, $token->refreshToken);

        self::assertNull($this->client->refreshFrontendCredentials($refreshedToken->token));
    }
}
