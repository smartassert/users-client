<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\DataProvider;

use GuzzleHttp\Psr7\Response;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;

trait InvalidJsonResponseExceptionDataProviderTrait
{
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
}