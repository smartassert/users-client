<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Exception;

use Psr\Http\Message\ResponseInterface;

class InvalidModelDataException extends \Exception
{
    /**
     * @param class-string $class
     * @param array<mixed> $payload
     */
    public function __construct(
        public readonly string $class,
        public readonly ResponseInterface $response,
        public readonly array $payload,
    ) {
        parent::__construct(sprintf('Data in response invalid for creating an instance of "%s"', $class));
    }
}
