<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class GetUserDefaultApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserDefaultApikey(): void
    {
        $frontendToken = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKey = $this->client->getUserDefaultApiKey($frontendToken);
        self::assertInstanceOf(ApiKey::class, $apiKey);

        $apiToken = $this->client->createApiToken($apiKey->key);
        self::assertInstanceOf(Token::class, $apiToken);
    }
}