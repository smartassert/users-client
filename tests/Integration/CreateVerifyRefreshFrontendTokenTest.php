<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Integration;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use SmartAssert\UsersClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\User;

class CreateVerifyRefreshFrontendTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        $this->client->createFrontendToken(md5((string) rand()), md5((string) rand()));
    }

    public function testCreateVerifyRefreshFrontendToken(): void
    {
        $parser = new Parser(new JoseEncoder());

        $token = $this->client->createFrontendToken(self::USER_EMAIL, self::USER_PASSWORD);
        self::assertInstanceOf(RefreshableToken::class, $token);

        $userFromToken = $this->client->verifyFrontendToken($token);
        self::assertInstanceOf(User::class, $userFromToken);
        self::assertSame(self::USER_EMAIL, $userFromToken->userIdentifier);

        $parsedToken = $parser->parse($token->token);
        self::assertInstanceOf(Plain::class, $parsedToken);
        self::assertSame(self::USER_EMAIL, $parsedToken->claims()->get('email'));
        self::assertSame($userFromToken->id, $parsedToken->claims()->get('sub'));

        $refreshedToken = $this->client->refreshFrontendToken($token->refreshToken);
        self::assertInstanceOf(RefreshableToken::class, $refreshedToken);

        $parsedRefreshedToken = $parser->parse($refreshedToken->token);
        self::assertInstanceOf(Plain::class, $parsedRefreshedToken);
        self::assertSame(self::USER_EMAIL, $parsedRefreshedToken->claims()->get('email'));
        self::assertSame($userFromToken->id, $parsedRefreshedToken->claims()->get('sub'));
    }
}
