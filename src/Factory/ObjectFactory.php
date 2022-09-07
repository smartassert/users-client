<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class ObjectFactory
{
    public function __construct(
        private readonly ApiKeyCollectionFactory $apiKeyCollectionFactory,
        private readonly RefreshableTokenFactory $refreshableTokenFactory,
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function createApiKeyCollectionFromArray(array $data): ApiKeyCollection
    {
        return $this->apiKeyCollectionFactory->fromArray($data);
    }

    /**
     * @param array<mixed> $data
     */
    public function createRefreshableTokenFromArray(array $data): ?RefreshableToken
    {
        return $this->refreshableTokenFactory->fromArray($data);
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
}
