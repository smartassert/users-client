<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Message\RequestInterface;

class RequestBuilder
{
    public const DEFAULT_AUTHORIZATION_HEADER_NAME = 'Authorization';
    public const DEFAULT_AUTHORIZATION_VALUE_PREFIX = 'Bearer ';

    public function __construct(
        private readonly string $authorizationHeaderName = self::DEFAULT_AUTHORIZATION_HEADER_NAME,
        private readonly string $authorizationValuePrefix = self::DEFAULT_AUTHORIZATION_VALUE_PREFIX,
    ) {
    }

    public function addJwtAuthorizationHeader(RequestInterface $request, string $token): RequestInterface
    {
        return $request->withHeader(
            $this->authorizationHeaderName,
            $this->authorizationValuePrefix . $token
        );
    }
}
