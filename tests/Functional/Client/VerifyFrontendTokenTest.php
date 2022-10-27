<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\TokenVerificationDataProviderTrait;

class VerifyFrontendTokenTest extends AbstractClientTest
{
    use CommonNonSuccessResponseDataProviderTrait;
    use TokenVerificationDataProviderTrait;

    public function testVerifyFrontendTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyFrontendToken(new Token('token'));
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testVerifyApiTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->verifyFrontendToken(new Token('token'));
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    /**
     * @dataProvider verifyTokenSuccessDataProvider
     */
    public function testVerifyFrontendToken(ResponseInterface $httpFixture, ?User $expected): void
    {
        $token = new Token(md5((string) rand()));

        $this->mockHandler->append($httpFixture);

        $returnValue = $this->client->verifyFrontendToken($token);
        self::assertEquals($expected, $returnValue);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token->token, $request->getHeaderLine('authorization'));
    }
}
