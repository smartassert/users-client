<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class User
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $userIdentifier
     */
    public function __construct(
        public readonly string $id,
        public readonly string $userIdentifier,
    ) {
    }
}
