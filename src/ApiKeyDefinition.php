<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\ServiceClient\ObjectFactory\ObjectDefinitionInterface;
use SmartAssert\ServiceClient\ObjectFactory\StringAccessor;
use SmartAssert\UsersClient\Model\ApiKey;

class ApiKeyDefinition implements ObjectDefinitionInterface
{
    public function getAccessors(): array
    {
        return [
            new StringAccessor('label'),
            new StringAccessor('key'),
        ];
    }

    public function isValid(array $data): bool
    {
        return (null === $data[0] || is_string($data[0]))
            && (isset($data[1]) && is_string($data[1]));
    }

    /**
     * @param array{0: ?string, 1: string} $data
     */
    public function create(array $data): ApiKey
    {
        return new ApiKey($data[0], $data[1]);
    }
}
