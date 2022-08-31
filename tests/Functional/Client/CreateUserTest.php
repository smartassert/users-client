<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\ValidJsonResponseDataProviderTrait;

class CreateUserTest extends AbstractClientTest
{
    use NetworkErrorExceptionDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
    use ValidJsonResponseDataProviderTrait;

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
}
