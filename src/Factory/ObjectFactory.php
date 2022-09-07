<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class ObjectFactory
{
    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function createApiKeyCollectionFromArray(array $data): ApiKeyCollection
    {
        $collection = [];

        foreach ($data as $apiKeyData) {
            if (is_array($apiKeyData)) {
                $apiKey = $this->createApiKeyFromArray($apiKeyData);

                if ($apiKey instanceof ApiKey) {
                    $collection[] = $apiKey;
                }
            }
        }

        return new ApiKeyCollection($collection);
    }

    /**
     * @param array<mixed> $data
     */
    public function createRefreshableTokenFromArray(array $data): ?RefreshableToken
    {
        $token = $this->arrayAccessor->getNonEmptyStringValue('token', $data);
        $refreshToken = $this->arrayAccessor->getNonEmptyStringValue('refresh_token', $data);

        return null === $token || null === $refreshToken ? null : new RefreshableToken($token, $refreshToken);
    }

    /**
     * @param array<mixed> $data
     */
    public function createTokenFromArray(array $data): ?Token
    {
        $token = $this->arrayAccessor->getNonEmptyStringValue('token', $data);

        return null === $token ? null : new Token($token);
    }

    /**
     * @param array<mixed> $data
     */
    public function createUserFromArray(array $data): ?User
    {
        $id = $this->arrayAccessor->getStringValue('id', $data);
        if (!is_string($id)) {
            return null;
        }

        $userIdentifier = $this->arrayAccessor->getStringValue('user-identifier', $data);
        if (!is_string($userIdentifier)) {
            return null;
        }

        return new User($id, $userIdentifier);
    }

    /**
     * @param array<mixed> $data
     */
    private function createApiKeyFromArray(array $data): ?ApiKey
    {
        if (!array_key_exists('label', $data)) {
            return null;
        }

        $label = $data['label'];
        if (!(null === $label || is_string($label))) {
            return null;
        }

        $key = $this->arrayAccessor->getStringValue('key', $data);
        if (!is_string($key)) {
            return null;
        }

        return new ApiKey($label, $key);
    }
}
