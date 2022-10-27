<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\DataProvider;

use GuzzleHttp\Psr7\Response;
use SmartAssert\UsersClient\Model\User;

trait TokenVerificationDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function verifyTokenSuccessDataProvider(): array
    {
        $userId = md5((string) rand());
        $userEmail = md5((string) rand()) . '@example.com';

        return [
            'unverified, HTTP 401' => [
                'httpFixture' => new Response(401),
                'expectedReturnValue' => null,
            ],
            'verified' => [
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode(['id' => $userId, 'user-identifier' => $userEmail])
                ),
                'expectedReturnValue' => new User($userId, $userEmail),
            ],
        ];
    }
}
