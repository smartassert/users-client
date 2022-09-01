<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class ApiKey
{
    public function __construct(
        public readonly ?string $label,
        public readonly string $key,
    ) {
    }

    public function isDefault(): bool
    {
        return null === $this->label;
    }
}
