<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\UsersClient\Model\FrontendToken;

class FrontendTokenFactory
{
    private const KEY_TOKEN = 'token';
    private const KEY_REFRESH_TOKEN = 'refresh_token';

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?FrontendToken
    {
        if (!array_key_exists(self::KEY_TOKEN, $data) || !array_key_exists(self::KEY_REFRESH_TOKEN, $data)) {
            return null;
        }

        $token = $data[self::KEY_TOKEN];
        $refreshToken = $data[self::KEY_REFRESH_TOKEN];

        return is_string($token) && is_string($refreshToken) ? new FrontendToken($token, $refreshToken) : null;
    }
}
