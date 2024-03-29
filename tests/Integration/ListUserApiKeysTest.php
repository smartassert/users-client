<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class ListUserApiKeysTest extends AbstractIntegrationTestCase
{
    public function testListUserApiKeysUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->listUserApiKeys(md5((string) rand()));
    }

    public function testListUserApiKeys(): void
    {
        $frontendToken = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendToken->token);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->client->listUserApiKeys($frontendToken->token);
        self::assertInstanceOf(ApiKeyCollection::class, $apiKeys);

        $defaultApiKey = $apiKeys->getDefault();
        self::assertInstanceOf(ApiKey::class, $defaultApiKey);

        $apiToken = $this->client->createApiToken($defaultApiKey->key);
        self::assertInstanceOf(Token::class, $apiToken);
    }
}
