<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use SmartAssert\UsersClient\Payload\Payload;
use SmartAssert\UsersClient\ServiceClient\Authentication\Authentication;

class Request
{
    private ?Authentication $authentication = null;
    private ?Payload $payload = null;

    public function __construct(
        public readonly string $method,
        public readonly string $url,
    ) {
    }

    public function getAuthentication(): ?Authentication
    {
        return $this->authentication;
    }

    public function withAuthentication(Authentication $authentication): Request
    {
        $new = clone $this;
        $new->authentication = $authentication;

        return $new;
    }

    public function getPayload(): ?Payload
    {
        return $this->payload;
    }

    public function withPayload(Payload $payload): Request
    {
        $new = clone $this;
        $new->payload = $payload;

        return $new;
    }
}
