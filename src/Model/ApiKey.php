<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

readonly class ApiKey implements SerializableInterface
{
    /**
     * @param ?non-empty-string $label
     * @param non-empty-string  $key
     */
    public function __construct(
        public ?string $label,
        public string $key,
    ) {
    }

    public function isDefault(): bool
    {
        return null === $this->label;
    }

    /**
     * @return array{label: ?non-empty-string, key: non-empty-string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'key' => $this->key,
        ];
    }
}
