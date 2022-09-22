<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\UsersClient\Client;
use SmartAssert\UsersClient\ObjectFactory;

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

        $this->client = new Client(
            'http://localhost:9080',
            $httpFactory,
            $httpFactory,
            new HttpClient(),
            new ObjectFactory(),
        );
    }
}
