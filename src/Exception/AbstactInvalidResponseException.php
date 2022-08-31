<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Exception;

use Psr\Http\Message\ResponseInterface;

abstract class AbstactInvalidResponseException extends \Exception
{
    public function __construct(
        public readonly string $context,
        public readonly string $expected,
        public readonly string $actual,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct(sprintf('Expected %s of "%s", got "%s"', $context, $expected, $actual));
    }
}
