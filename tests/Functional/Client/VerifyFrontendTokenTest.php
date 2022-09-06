<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\Token;

class VerifyFrontendTokenTest extends AbstractClientTest
{
    public function testVerifyFrontendTokenThrowsClientExceptionInterface(): void
    {
        $this->mockHandler->append(new ConnectException('Exception message', new Request('GET', '/')));

        $this->expectException(ClientExceptionInterface::class);

        $this->client->verifyFrontendToken(new Token('token'));
    }

    /**
     * @dataProvider verifyDataProvider
     */
    public function testVerifyFrontendToken(ResponseInterface $httpFixture, bool $expected): void
    {
        $token = new Token(md5((string) rand()));

        $this->mockHandler->append($httpFixture);

        $returnValue = $this->client->verifyFrontendToken($token);
        self::assertSame($expected, $returnValue);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token->token, $request->getHeaderLine('authorization'));
    }

    /**
     * @return array<mixed>
     */
    public function verifyDataProvider(): array
    {
        return [
            'unverified, HTTP 401' => [
                'httpFixture' => new Response(401),
                'expected' => false,
            ],
            'unverified, HTTP 500' => [
                'httpFixture' => new Response(500),
                'expected' => false,
            ],
            'verified' => [
                'httpFixture' => new Response(200),
                'expected' => true,
            ],
        ];
    }
}
