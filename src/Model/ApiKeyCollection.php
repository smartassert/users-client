<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

class ApiKeyCollection
{
    /**
     * @param ApiKey[] $apiKeys
     */
    public function __construct(
        private readonly array $apiKeys,
    ) {
    }

    public function getDefault(): ?ApiKey
    {
        foreach ($this->apiKeys as $apiKey) {
            if ($apiKey->isDefault()) {
                return $apiKey;
            }
        }

        return null;
    }
}
