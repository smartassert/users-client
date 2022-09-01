<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

class Routes
{
    public const DEFAULT_VERIFY_API_TOKEN_PATH = '/api/token/verify';
    public const DEFAULT_CREATE_USER_PATH = '/admin/user/create';
    public const DEFAULT_CREATE_FRONTEND_TOKEN_PATH = '/frontend/token/create';
    public const DEFAULT_LIST_USER_API_KEYS_PATH = '/frontend/apikey/list';
    public const DEFAULT_VERIFY_FRONTEND_TOKEN_PATH = '/frontend/token/verify';
    public const DEFAULT_REFRESH_FRONTEND_TOKEN_PATH = '/frontend/token/refresh';
    public const DEFAULT_CREATE_API_TOKEN_PATH = '/api/token/create';
    public const DEFAULT_REVOKE_FRONTEND_REFRESH_TOKEN_PATH = '/admin/frontend/refresh-token/revoke';

    /**
     * @param non-empty-string $baseUrl
     * @param non-empty-string $verifyApiTokenPath
     * @param non-empty-string $createUserPath
     * @param non-empty-string $createFrontendTokenPath
     */
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $verifyApiTokenPath = self::DEFAULT_VERIFY_API_TOKEN_PATH,
        private readonly string $createUserPath = self::DEFAULT_CREATE_USER_PATH,
        private readonly string $createFrontendTokenPath = self::DEFAULT_CREATE_FRONTEND_TOKEN_PATH,
        private readonly string $listUserApiKeyPath = self::DEFAULT_LIST_USER_API_KEYS_PATH,
        private readonly string $verifyFrontendTokenPath = self::DEFAULT_VERIFY_FRONTEND_TOKEN_PATH,
        private readonly string $refreshFrontendTokenPath = self::DEFAULT_REFRESH_FRONTEND_TOKEN_PATH,
        private readonly string $createApiTokenPath = self::DEFAULT_CREATE_API_TOKEN_PATH,
        private readonly string $revokeFrontendRefreshTokenPath = self::DEFAULT_REVOKE_FRONTEND_REFRESH_TOKEN_PATH,
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

    /**
     * @return non-empty-string
     */
    public function getCreateFrontendTokenUrl(): string
    {
        return $this->baseUrl . $this->createFrontendTokenPath;
    }

    /**
     * @return non-empty-string
     */
    public function getListUserApiKeysUrl(): string
    {
        return $this->baseUrl . $this->listUserApiKeyPath;
    }

    /**
     * @return non-empty-string
     */
    public function getVerifyFrontendTokenUrl(): string
    {
        return $this->baseUrl . $this->verifyFrontendTokenPath;
    }

    /**
     * @return non-empty-string
     */
    public function getRefreshFrontendTokenUrl(): string
    {
        return $this->baseUrl . $this->refreshFrontendTokenPath;
    }

    /**
     * @return non-empty-string
     */
    public function getCreateApiTokenUrl(): string
    {
        return $this->baseUrl . $this->createApiTokenPath;
    }

    /**
     * @return non-empty-string
     */
    public function getRevokeFrontendRefreshTokenUrl(): string
    {
        return $this->baseUrl . $this->revokeFrontendRefreshTokenPath;
    }
}
