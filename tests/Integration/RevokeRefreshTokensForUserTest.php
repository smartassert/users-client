<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\User;

class RevokeRefreshTokensForUserTest extends AbstractIntegrationTestCase
{
    public function testRevokeUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->revokeFrontendRefreshTokensForUser(md5((string) rand()), md5((string) rand()));
    }

    public function testRevokeSuccess(): void
    {
        $token = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($token instanceof FrontendCredentials);

        $userFromToken = $this->client->verifyFrontendToken($token->token);
        \assert($userFromToken instanceof User);

        $refreshedToken = $this->client->refreshFrontendCredentials($token->refreshToken);
        \assert($refreshedToken instanceof FrontendCredentials);

        $this->client->revokeFrontendRefreshTokensForUser(self::ADMIN_TOKEN, $userFromToken->id);

        self::assertNull($this->client->refreshFrontendCredentials($refreshedToken->token));
    }
}
