<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\Token;
use Symfony\Component\Uid\Ulid;

class CreateUserTest extends AbstractIntegrationTest
{
    public function testCreateUserAlreadyExists(): void
    {
        self::expectException(UserAlreadyExistsException::class);

        $this->client->createUser(self::ADMIN_TOKEN, self::USER_EMAIL, self::USER_PASSWORD);
    }

    public function testCreateSuccess(): void
    {
        $email = new Ulid() . '@example.com';
        $password = md5((string) rand());

        $responseData = $this->client->createUser(self::ADMIN_TOKEN, $email, $password);
        self::assertArrayHasKey('user', $responseData);

        $userData = $responseData['user'];
        self::assertIsArray($userData);
        self::assertArrayHasKey('id', $userData);

        self::assertIsString($userData['id']);
        self::assertTrue(Ulid::isValid($userData['id']));
        self::assertArrayHasKey('user-identifier', $userData);
        self::assertSame($email, $userData['user-identifier']);

        $frontendToken = $this->client->createFrontendToken($email, $password);
        self::assertInstanceOf(Token::class, $frontendToken);
    }
}
