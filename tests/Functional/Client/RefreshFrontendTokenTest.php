<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\ValidJsonResponseDataProviderTrait;

class RefreshFrontendTokenTest extends AbstractClientTest
{
    use NetworkErrorExceptionDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use ValidJsonResponseDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testRefreshFrontendTokenThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->refreshFrontendToken(new RefreshableToken(md5((string) rand()), md5((string) rand())));
    }

    /**
     * @dataProvider validJsonResponseDataProvider
     *
     * @param array<mixed> $expected
     */
    public function testRefreshFrontendTokenSuccess(ResponseInterface $httpFixture, array $expected): void
    {
        $refreshToken = new RefreshableToken(md5((string) rand()), md5((string) rand()));

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->refreshFrontendToken($refreshToken);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('content-type'));
        self::assertSame(
            json_encode(['refresh_token' => $refreshToken->refreshToken]),
            $request->getBody()->getContents()
        );
    }
}
