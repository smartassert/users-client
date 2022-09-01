<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class FrontendToken
{
    public function __construct(
        public readonly string $token,
        public readonly string $refreshToken,
    ) {
    }
}
