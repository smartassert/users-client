<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

readonly class RefreshableToken extends Token implements SerializableInterface
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        string $token,
        public string $refreshToken,
    ) {
        parent::__construct($token);
    }

    /**
     * @return array{token: non-empty-string, refresh_token: non-empty-string}
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['refresh_token' => $this->refreshToken]);
    }
}
