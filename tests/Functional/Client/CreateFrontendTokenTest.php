<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;

class CreateFrontendTokenTest extends AbstractClientTest
{
    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateFrontendTokenThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->createFrontendToken('email', 'password');
    }

    /**
     * @param array<mixed> $expected
     *
     * @dataProvider validJsonResponseDataProvider
     */
    public function testCreateFrontendTokenSuccess(ResponseInterface $httpFixture, array $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->createFrontendToken('email', 'password');

        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public function networkErrorExceptionDataProvider(): array
    {
        return [
            'network error' => [
                'httpFixture' => new ConnectException('Exception message', new Request('GET', '/')),
                'expectedExceptionClass' => ClientExceptionInterface::class,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function invalidJsonResponseExceptionDataProvider(): array
    {
        return [
            'invalid response content type' => [
                'httpFixture' => new Response(200, ['content-type' => 'text/plain']),
                'expectedExceptionClass' => InvalidResponseContentException::class,
            ],
            'invalid response data' => [
                'httpFixture' => new Response(200, ['content-type' => 'application/json'], '1'),
                'expectedExceptionClass' => InvalidResponseDataException::class,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validJsonResponseDataProvider(): array
    {
        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ])
                ),
                'expected' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                    'key3' => 'value3',
                ],
            ],
        ];
    }
}
