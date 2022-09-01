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

class ListUserApiKeysTest extends AbstractClientTest
{
    use NetworkErrorExceptionDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateUserThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->listUserApiKeys('token');
    }

    /**
     * @dataProvider listApiKeysSuccessDataProvider
     */
    public function testListApiKeySuccess(ResponseInterface $httpFixture, ApiKeyCollection $expected): void
    {
        $token = md5((string) rand());

        $this->mockHandler->append($httpFixture);

        $actual = $this->client->listUserApiKeys($token);
        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
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
