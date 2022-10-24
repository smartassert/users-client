<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\ServiceClient\ObjectFactory\ObjectDefinitionInterface;
use SmartAssert\ServiceClient\ObjectFactory\StringAccessor;
use SmartAssert\UsersClient\Model\User;

class UserDefinition implements ObjectDefinitionInterface
{
    public function getAccessors(): array
    {
        return [
            new StringAccessor('id'),
            new StringAccessor('user-identifier'),
        ];
    }

    public function isValid(array $data): bool
    {
        return (isset($data[0]) && is_string($data[0]))
            && (isset($data[1]) && is_string($data[1]));
    }

    /**
     * @param array{0: string, 1: string} $data
     */
    public function create(array $data): User
    {
        return new User($data[0], $data[1]);
    }
}
