<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class Token
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public readonly string $token,
    ) {
    }
}
