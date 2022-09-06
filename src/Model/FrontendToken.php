<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class FrontendToken
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        public readonly string $token,
        public readonly string $refreshToken,
    ) {
    }
}
