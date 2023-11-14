<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

readonly class User implements SerializableInterface
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $userIdentifier
     */
    public function __construct(
        public string $id,
        public string $userIdentifier,
    ) {
    }

    /**
     * @return array{id: non-empty-string, user-identifier: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user-identifier' => $this->userIdentifier,
        ];
    }
}
