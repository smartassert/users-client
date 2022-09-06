<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\TokenVerificationDataProviderTrait;

class VerifyApiTokenTest extends AbstractClientTest
{
    use TokenVerificationDataProviderTrait;

    public function testVerifyApiTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyApiToken(new Token('token'));
    }

    /**
     * @dataProvider verifyTokenDataProvider
     */
    public function testVerifyApiToken(ResponseInterface $httpFixture, ?User $expectedReturnValue): void
    {
        $token = new Token(md5((string) rand()));

        $this->mockHandler->append($httpFixture);

        $returnValue = $this->client->verifyApiToken($token);
        self::assertEquals($expectedReturnValue, $returnValue);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token->token, $request->getHeaderLine('authorization'));
    }
}
