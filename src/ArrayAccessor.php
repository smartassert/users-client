<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

class ArrayAccessor
{
    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     */
    public function getStringValue(string $key, array $data): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     *
     * @return null|non-empty-string
     */
    public function getNonEmptyStringValue(string $key, array $data): ?string
    {
        $value = trim((string) $this->getStringValue($key, $data));

        return '' === $value ? null : $value;
    }
}
