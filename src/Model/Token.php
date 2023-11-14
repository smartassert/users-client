<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

readonly class Token implements SerializableInterface
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        readonly string $token,
    ) {
    }

    /**
     * @return array{token: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
