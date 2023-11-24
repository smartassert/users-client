<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

readonly class FrontendCredentials implements SerializableInterface
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     * @param non-empty-string $apiKey
     */
    public function __construct(
        public string $token,
        public string $refreshToken,
        public string $apiKey,
    ) {
    }

    /**
     * @return array{token: non-empty-string, refresh_token: non-empty-string, api_key: non-empty-string}
     */
    public function toArray(): array
    {
        return ['token' => $this->token, 'refresh_token' => $this->refreshToken, 'api_key' => $this->apiKey];
    }
}
