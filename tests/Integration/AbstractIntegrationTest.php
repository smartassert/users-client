<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersClient\ApiKeyCollectionFactory;
use SmartAssert\UsersClient\ApiKeyFactory;
use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\RefreshableTokenFactory;
use SmartAssert\UsersClient\RequestBuilder;
use SmartAssert\UsersClient\Routes;
use SmartAssert\UsersClient\TokenFactory;
use SmartAssert\UsersClient\UserFactory;

abstract class AbstractIntegrationTest extends TestCase
{
    protected const ADMIN_TOKEN = 'primary_admin_token';
    protected const USER_EMAIL = 'user@example.com';
    protected const USER_PASSWORD = 'password';

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();
        $arrayAccessor = new ArrayAccessor();

        $this->client = new Client(
            $httpFactory,
            $httpFactory,
            new RequestBuilder(),
            new HttpClient(),
            new Routes(
                'http://localhost:9080',
            ),
            new ApiKeyCollectionFactory(
                new ApiKeyFactory($arrayAccessor),
            ),
            new RefreshableTokenFactory(),
            new TokenFactory($arrayAccessor),
            new UserFactory($arrayAccessor)
        );
    }
}
