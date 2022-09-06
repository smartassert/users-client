<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\UsersClient\Model\ApiToken;

class ApiTokenFactory
{
    private const KEY_TOKEN = 'token';

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?ApiToken
    {
        $token = $this->getNonEmptyStringValue(self::KEY_TOKEN, $data);

        return null === $token ? null : new ApiToken($token);
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     *
     * @return null|non-empty-string
     */
    private function getNonEmptyStringValue(string $key, array $data): ?string
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key] ?? null;
        $value = is_string($value) ? trim($value) : null;

        return '' === $value ? null : $value;
    }
}
