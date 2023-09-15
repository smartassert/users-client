<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\TokenVerificationDataProviderTrait;

class VerifyApiTokenTest extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;
    use TokenVerificationDataProviderTrait;

    public function testVerifyApiTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyApiToken('token');
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testVerifyApiTokenThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->verifyApiToken('token');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    public function testVerifyApiTokenInvalidResponseData(): void
    {
        $this->doInvalidResponseDataTest(
            function () {
                $this->client->verifyApiToken('token');
            },
            User::class
        );
    }

    /**
     * @dataProvider verifyTokenSuccessDataProvider
     */
    public function testVerifyApiToken(ResponseInterface $httpFixture, ?User $expectedReturnValue): void
    {
        $token = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $returnValue = $this->client->verifyApiToken($token);
        self::assertEquals($expectedReturnValue, $returnValue);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }
}
