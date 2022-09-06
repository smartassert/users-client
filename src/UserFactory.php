<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\UsersClient\Model\User;

class UserFactory
{
    private const KEY_ID = 'id';
    private const KEY_USER_IDENTIFIER = 'user-identifier';

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?User
    {
        if (!array_key_exists(self::KEY_ID, $data)) {
            return null;
        }

        $id = $data[self::KEY_ID];
        if (!is_string($id)) {
            return null;
        }

        if (!array_key_exists(self::KEY_USER_IDENTIFIER, $data)) {
            return null;
        }

        $userIdentifier = $data[self::KEY_USER_IDENTIFIER];
        if (!is_string($userIdentifier)) {
            return null;
        }

        return new User($id, $userIdentifier);
    }
}
