<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Exception;

use Psr\Http\Message\ResponseInterface;

class InvalidResponseDataException extends AbstactInvalidResponseException
{
    public function __construct(
        string $expected,
        string $actual,
        ResponseInterface $response
    ) {
        parent::__construct('payload data type', $expected, $actual, $response);
    }
}
