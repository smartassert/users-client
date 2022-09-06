<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class CreateVerifyApiTokenTest extends AbstractIntegrationTest
{
    public function testCreateVerifyApiToken(): void
    {
        $frontendToken = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->client->listUserApiKeys($frontendToken);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $this->client->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $this->client->verifyApiToken($apiToken);
        \assert($apiTokenUser instanceof User);

        self::assertSame($frontendTokenUser->userIdentifier, $apiTokenUser->userIdentifier);
    }
}
