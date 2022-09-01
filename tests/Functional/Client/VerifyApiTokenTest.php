<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class VerifyApiTokenTest extends AbstractClientTest
{
    public function testVerifyApiTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyApiToken('token');
    }

    /**
     * @dataProvider verifyDataProvider
     */
    public function testVerifyApiToken(ResponseInterface $httpFixture, ?string $expectedReturnValue): void
    {
        $token = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $returnValue = $this->client->verifyApiToken($token);
        self::assertSame($expectedReturnValue, $returnValue);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }

    /**
     * @return array<mixed>
     */
    public function verifyDataProvider(): array
    {
        $userId = md5((string) rand());

        return [
            'unverified, HTTP 401' => [
                'httpFixture' => new Response(401),
                'expectedReturnValue' => null,
            ],
            'unverified, HTTP 500' => [
                'httpFixture' => new Response(500),
                'expectedReturnValue' => null,
            ],
            'verified' => [
                'httpFixture' => new Response(200, [], $userId),
                'expectedReturnValue' => $userId,
            ],
        ];
    }
}
