<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\ServiceClient\Authentication;

class Authentication
{
    public function __construct(
        public readonly string $value,
    ) {
    }
}
