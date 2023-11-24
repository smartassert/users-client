<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class CreateVerifyApiTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->createApiToken(md5((string) rand()));
    }

    public function testCreateVerifyApiToken(): void
    {
        $frontendCredentials = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        \assert($frontendCredentials instanceof FrontendCredentials);

        $frontendTokenUser = $this->client->verifyFrontendToken($frontendCredentials->token);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $this->client->listUserApiKeys($frontendCredentials->token);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $this->client->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $this->client->verifyApiToken($apiToken->token);
        \assert($apiTokenUser instanceof User);

        self::assertSame($frontendTokenUser->userIdentifier, $apiTokenUser->userIdentifier);
    }
}
