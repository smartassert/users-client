<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\UsersClient\Model\Token;

class TokenFactory
{
    private const KEY_TOKEN = 'token';

    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?Token
    {
        $token = $this->arrayAccessor->getNonEmptyStringValue(self::KEY_TOKEN, $data);

        return null === $token ? null : new Token($token);
    }
}
