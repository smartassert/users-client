<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateUserTest extends AbstractClientTest
{
    use NetworkErrorExceptionDataProviderTrait;

    /**
     * @dataProvider networkErrorExceptionDataProvider
     * @dataProvider invalidJsonResponseExceptionDataProvider
     * @dataProvider userAlreadyExistsExceptionDataProvider
     *
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public function testCreateUserThrowsException(
        ResponseInterface|ClientExceptionInterface $httpFixture,
        string $expectedExceptionClass,
    ): void {
        $this->mockHandler->append($httpFixture);

        $this->expectException($expectedExceptionClass);

        $this->client->createUser('admin token', 'email', 'password');
    }

    /**
     * @return array<mixed>
     */
    public function userAlreadyExistsExceptionDataProvider(): array
    {
        return [
            'already exists' => [
                'httpFixture' => new Response(409, ['content-type' => 'application/json'], '1'),
                'expectedExceptionClass' => UserAlreadyExistsException::class,
            ],
        ];
    }

    /**
     * @param array<mixed> $expected
     *
     * @dataProvider validJsonResponseDataProvider
     */
    public function testCreateUserSuccess(ResponseInterface $httpFixture, array $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->createUser('admin token', 'email', 'password');

        self::assertEquals($expected, $actual);
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
