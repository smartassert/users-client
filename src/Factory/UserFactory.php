<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Model\User;

class UserFactory
{
    private const KEY_ID = 'id';
    private const KEY_USER_IDENTIFIER = 'user-identifier';

    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?User
    {
        $id = $this->arrayAccessor->getStringValue(self::KEY_ID, $data);
        if (!is_string($id)) {
            return null;
        }

        $userIdentifier = $this->arrayAccessor->getStringValue(self::KEY_USER_IDENTIFIER, $data);
        if (!is_string($userIdentifier)) {
            return null;
        }

        return new User($id, $userIdentifier);
    }
}
