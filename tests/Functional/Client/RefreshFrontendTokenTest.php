<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
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

        $this->client->listUserApiKeys('token');
    }

    /**
     * @dataProvider validJsonResponseDataProvider
     *
     * @param array<mixed> $expected
     */
    public function testRefreshFrontendTokenSuccess(ResponseInterface $httpFixture, array $expected): void
    {
        $refreshToken = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->refreshFrontendToken($refreshToken);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/json', $request->getHeaderLine('content-type'));
        self::assertSame(json_encode(['refresh_token' => $refreshToken]), $request->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function listApiKeysSuccessDataProvider(): array
    {
        return [
            'single' => [
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        [
                            'label' => null,
                            'key' => 'key1',
                        ],
                    ])
                ),
                'expected' => new ApiKeyCollection([
                    new ApiKey(null, 'key1'),
                ]),
            ],
            'multiple' => [
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        [
                            'label' => null,
                            'key' => 'key2',
                        ],
                        [
                            'label' => 'user defined label 1',
                            'key' => 'key3',
                        ],
                        [
                            'label' => 'user defined label 2',
                            'key' => 'key4',
                        ],
                    ])
                ),
                'expected' => new ApiKeyCollection([
                    new ApiKey(null, 'key2'),
                    new ApiKey('user defined label 1', 'key3'),
                    new ApiKey('user defined label 2', 'key4'),
                ]),
            ],
        ];
    }
}
