<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\UsersClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateUserTest extends AbstractClientTest
{
    use CommonNonSuccessResponseDataProviderTrait;
    use InvalidJsonResponseExceptionDataProviderTrait;
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

    public function testCreateUserInvalidResponseData(): void
    {
        $this->doInvalidResponseDataTest(
            function () {
                $this->client->createUser('admin token', 'email', 'password');
            },
            User::class
        );
    }

    /**
     * @dataProvider commonNonSuccessResponseDataProvider
     */
    public function testCreateUserThrowsNonSuccessResponseException(ResponseInterface $httpFixture): void
    {
        $this->mockHandler->append($httpFixture);

        try {
            $this->client->createUser('admin token', 'email', 'password');
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame($httpFixture, $e->response);
        }
    }

    /**
     * @dataProvider createUserSuccessDataProvider
     */
    public function testCreateUserSuccess(ResponseInterface $httpFixture, User $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $email = 'email value';
        $password = 'password value';
        $adminToken = 'admin token';

        $actual = $this->client->createUser($adminToken, $email, $password);

        self::assertEquals($expected, $actual);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('content-type'));
        self::assertSame($adminToken, $request->getHeaderLine('authorization'));
        self::assertSame(
            http_build_query(['email' => $email, 'password' => $password]),
            $request->getBody()->getContents()
        );
    }

    /**
     * @return array<mixed>
     */
    public function createUserSuccessDataProvider(): array
    {
        $id = md5((string) rand());
        $userIdentifier = md5((string) rand()) . '@example.com';

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'user' => [
                            'id' => $id,
                            'user-identifier' => $userIdentifier,
                        ],
                    ])
                ),
                'expected' => new User($id, $userIdentifier),
            ],
        ];
    }
}
