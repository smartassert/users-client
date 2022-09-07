<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class ObjectFactory
{
    public function __construct(
        private readonly ApiKeyCollectionFactory $apiKeyCollectionFactory,
        private readonly RefreshableTokenFactory $refreshableTokenFactory,
        private readonly TokenFactory $tokenFactory,
        private readonly UserFactory $userFactory,
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
        return $this->tokenFactory->fromArray($data);
    }

    /**
     * @param array<mixed> $data
     */
    public function createUserFromArray(array $data): ?User
    {
        return $this->userFactory->fromArray($data);
    }
}
