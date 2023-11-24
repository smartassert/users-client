<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\User;

class CreateVerifyRefreshFrontendTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->createFrontendCredentials(md5((string) rand()), md5((string) rand()));
    }

    public function testCreateVerifyRefreshFrontendToken(): void
    {
        $parser = new Parser(new JoseEncoder());

        $token = $this->client->createFrontendCredentials(self::USER_EMAIL, self::USER_PASSWORD);
        self::assertInstanceOf(FrontendCredentials::class, $token);

        $userFromToken = $this->client->verifyFrontendToken($token->token);
        self::assertInstanceOf(User::class, $userFromToken);
        self::assertSame(self::USER_EMAIL, $userFromToken->userIdentifier);

        $parsedToken = $parser->parse($token->token);
        self::assertInstanceOf(Plain::class, $parsedToken);
        self::assertSame(self::USER_EMAIL, $parsedToken->claims()->get('email'));
        self::assertSame($userFromToken->id, $parsedToken->claims()->get('sub'));

        $refreshedToken = $this->client->refreshFrontendCredentials($token->refreshToken);
        self::assertInstanceOf(FrontendCredentials::class, $refreshedToken);

        $parsedRefreshedToken = $parser->parse($refreshedToken->token);
        self::assertInstanceOf(Plain::class, $parsedRefreshedToken);
        self::assertSame(self::USER_EMAIL, $parsedRefreshedToken->claims()->get('email'));
        self::assertSame($userFromToken->id, $parsedRefreshedToken->claims()->get('sub'));
    }
}
