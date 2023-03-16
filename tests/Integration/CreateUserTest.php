<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use Symfony\Component\Uid\Ulid;

class CreateUserTest extends AbstractIntegrationTestCase
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

        $user = $this->client->createUser(self::ADMIN_TOKEN, $email, $password);
        self::assertInstanceOf(User::class, $user);
        self::assertTrue(Ulid::isValid($user->id));
        self::assertSame($email, $user->userIdentifier);

        $frontendToken = $this->client->createFrontendToken($email, $password);
        self::assertInstanceOf(Token::class, $frontendToken);
    }
}
