<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\ServiceClient\ObjectFactory\NonEmptyStringAccessor;
use SmartAssert\ServiceClient\ObjectFactory\ObjectDefinitionInterface;
use SmartAssert\UsersClient\Model\RefreshableToken;

class RefreshableTokenDefinition implements ObjectDefinitionInterface
{
    public function getAccessors(): array
    {
        return [
            new NonEmptyStringAccessor('token'),
            new NonEmptyStringAccessor('refresh_token'),
        ];
    }

    public function isValid(array $data): bool
    {
        return (isset($data[0]) && is_string($data[0]) && '' !== $data[0])
            && (isset($data[1]) && is_string($data[1]) && '' !== $data[1]);
    }

    /**
     * @param array{0: non-empty-string, 1: non-empty-string} $data
     */
    public function create(array $data): RefreshableToken
    {
        return new RefreshableToken($data[0], $data[1]);
    }
}
