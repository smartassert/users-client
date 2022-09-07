<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\Model\RefreshableToken;

class RefreshableTokenFactory
{
    private const KEY_TOKEN = 'token';
    private const KEY_REFRESH_TOKEN = 'refresh_token';

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?RefreshableToken
    {
        $token = $this->getNonEmptyStringValue(self::KEY_TOKEN, $data);
        $refreshToken = $this->getNonEmptyStringValue(self::KEY_REFRESH_TOKEN, $data);

        return null === $token || null === $refreshToken ? null : new RefreshableToken($token, $refreshToken);
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
