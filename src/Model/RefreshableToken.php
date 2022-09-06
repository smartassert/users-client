<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class RefreshableToken extends Token
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        string $token,
        public readonly string $refreshToken,
    ) {
        parent::__construct($token);
    }
}
