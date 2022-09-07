<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;

class ApiKeyCollectionFactory
{
    public function __construct(
        private readonly ApiKeyFactory $apiKeyFactory,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ApiKeyCollection
    {
        $collection = [];

        foreach ($data as $apiKeyData) {
            if (is_array($apiKeyData)) {
                $apiKey = $this->apiKeyFactory->fromArray($apiKeyData);

                if ($apiKey instanceof ApiKey) {
                    $collection[] = $apiKey;
                }
            }
        }

        return new ApiKeyCollection($collection);
    }
}
