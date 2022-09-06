<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $userIdentifier,
    ) {
    }
}
