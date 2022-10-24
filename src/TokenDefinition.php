<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\ServiceClient\ObjectFactory\NonEmptyStringAccessor;
use SmartAssert\ServiceClient\ObjectFactory\ObjectDefinitionInterface;
use SmartAssert\UsersClient\Model\Token;

class TokenDefinition implements ObjectDefinitionInterface
{
    public function getAccessors(): array
    {
        return [
            new NonEmptyStringAccessor('token'),
        ];
    }

    public function isValid(array $data): bool
    {
        return isset($data[0]) && is_string($data[0]) && '' !== $data[0];
    }

    /**
     * @param array{0: non-empty-string} $data
     */
    public function create(array $data): Token
    {
        return new Token($data[0]);
    }
}
