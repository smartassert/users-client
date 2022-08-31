<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

class Routes
{
    public const DEFAULT_VERIFY_API_TOKEN_PATH = '/api/token/verify';
    public const DEFAULT_CREATE_USER_PATH = '/admin/user/create';

    /**
     * @param non-empty-string $baseUrl
     * @param non-empty-string $verifyApiTokenPath
     * @param non-empty-string $createUserPath
     */
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $verifyApiTokenPath = self::DEFAULT_VERIFY_API_TOKEN_PATH,
        private readonly string $createUserPath = self::DEFAULT_CREATE_USER_PATH,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getVerifyApiTokenUrl(): string
    {
        return $this->baseUrl . $this->verifyApiTokenPath;
    }

    /**
     * @return non-empty-string
     */
    public function getCreateUserUrl(): string
    {
        return $this->baseUrl . $this->createUserPath;
    }
}
