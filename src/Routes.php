<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

class Routes
{
    public const DEFAULT_VERIFY_API_TOKEN_PATH = '/api/token/verify';

    public function __construct(
        private string $baseUrl,
        private string $verifyApiTokenPath = self::DEFAULT_VERIFY_API_TOKEN_PATH,
    ) {
    }

    public function getVerifyApiTokenUrl(): string
    {
        return $this->baseUrl . $this->verifyApiTokenPath;
    }
}
