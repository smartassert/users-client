<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Payload;

class JsonPayload extends Payload
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('application/json', (string) json_encode($data));
    }
}
