<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Authentication;

class Authentication
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
