<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\UsersClient\Client;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected const ADMIN_TOKEN = 'primary_admin_token';
    protected const USER_EMAIL = 'user@example.com';
    protected const USER_PASSWORD = 'password';

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new Client(
            'http://localhost:9080',
            new ServiceClient($httpFactory, $httpFactory, new HttpClient()),
        );
    }
}
