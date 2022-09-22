<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Payload;

class Payload
{
    public function __construct(
        public readonly string $contentType,
        public readonly string $data,
    ) {
    }
}
