<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

/**
 * @implements \IteratorAggregate<ApiKey>
 */
class ApiKeyCollection implements \IteratorAggregate
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

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->apiKeys);
    }
}
