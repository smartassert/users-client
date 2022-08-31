<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Exception;

use Psr\Http\Message\ResponseInterface;

class InvalidResponseContentException extends AbstactInvalidResponseException
{
    public function __construct(
        string $expected,
        string $actual,
        ResponseInterface $response
    ) {
        parent::__construct('content-type header', $expected, $actual, $response);
    }
}
