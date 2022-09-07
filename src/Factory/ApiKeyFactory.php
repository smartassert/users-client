<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Factory;

use SmartAssert\UsersClient\ArrayAccessor;
use SmartAssert\UsersClient\Model\ApiKey;

class ApiKeyFactory
{
    private const KEY_LABEL = 'label';
    private const KEY_KEY = 'key';

    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function fromArray(array $data): ?ApiKey
    {
        if (!array_key_exists(self::KEY_LABEL, $data)) {
            return null;
        }

        $label = $data[self::KEY_LABEL];
        if (!(null === $label || is_string($label))) {
            return null;
        }

        $key = $this->arrayAccessor->getStringValue(self::KEY_KEY, $data);
        if (!is_string($key)) {
            return null;
        }

        return new ApiKey($label, $key);
    }
}
