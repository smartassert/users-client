<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

class UserCreationOutcome
{
    /**
     * @param array<mixed> $userData
     */
    public function __construct(
        public readonly bool $wasCreated,
        public readonly array $userData,
    ) {
    }
}
