<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Model;

use SmartAssert\ServiceClient\SerializableInterface;

/**
 * @implements \IteratorAggregate<ApiKey>
 *
 * @phpstan-import-type SerializedApiKey from ApiKey
 */
readonly class ApiKeyCollection implements \IteratorAggregate, SerializableInterface
{
    /**
     * @param ApiKey[] $apiKeys
     */
    public function __construct(
        private array $apiKeys,
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

    /**
     * @return array<SerializedApiKey>
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->apiKeys as $apiKey) {
            $data[] = $apiKey->toArray();
        }

        return $data;
    }
}
