<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class GetUserDefaultApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserDefaultApikeyUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->getUserDefaultApiKey(md5((string) rand()));
    }

    public function testGetUserDefaultApikey(): void
    {
        $frontendCredentials = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendCredentials instanceof FrontendCredentials);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendCredentials->token);
        \assert($frontendTokenUser instanceof User);

        $apiKey = $this->client->getUserDefaultApiKey($frontendCredentials->token);
        self::assertInstanceOf(ApiKey::class, $apiKey);

        $apiToken = $this->client->createApiToken($apiKey->key);
        self::assertInstanceOf(Token::class, $apiToken);
    }
}
