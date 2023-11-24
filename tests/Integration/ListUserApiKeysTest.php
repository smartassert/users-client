<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\FrontendCredentials;
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
        $frontendCredentials = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendCredentials instanceof FrontendCredentials);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendCredentials->token);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->client->listUserApiKeys($frontendCredentials->token);
        self::assertInstanceOf(ApiKeyCollection::class, $apiKeys);

        $defaultApiKey = $apiKeys->getDefault();
        self::assertInstanceOf(ApiKey::class, $defaultApiKey);

        $apiToken = $this->client->createApiToken($defaultApiKey->key);
        self::assertInstanceOf(Token::class, $apiToken);
    }
}
