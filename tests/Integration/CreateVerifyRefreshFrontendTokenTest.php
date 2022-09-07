<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\User;

class CreateVerifyRefreshFrontendTokenTest extends AbstractIntegrationTest
{
    public function testCreateVerifyRefreshFrontendToken(): void
    {
        $token = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        self::assertInstanceOf(RefreshableToken::class, $token);

        $userFromToken = $this->client->verifyFrontendToken($token);
        self::assertInstanceOf(User::class, $userFromToken);
        self::assertSame(self::USER_EMAIL, $userFromToken->userIdentifier);

        $refreshedToken = $this->client->refreshFrontendToken($token);
        self::assertInstanceOf(RefreshableToken::class, $refreshedToken);
        self::assertEquals($token, $refreshedToken);

        $userFromRefreshedToken = $this->client->verifyFrontendToken($refreshedToken);
        self::assertEquals($userFromToken, $userFromRefreshedToken);
    }
}
