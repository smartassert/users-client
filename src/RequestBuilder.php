<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RequestBuilder
{
    public const DEFAULT_AUTHORIZATION_HEADER_NAME = 'Authorization';
    public const DEFAULT_AUTHORIZATION_VALUE_PREFIX = 'Bearer ';

    private RequestInterface $request;

    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private string $authorizationHeaderName = self::DEFAULT_AUTHORIZATION_HEADER_NAME,
        private string $authorizationValuePrefix = self::DEFAULT_AUTHORIZATION_VALUE_PREFIX,
    ) {
    }

    public function create(string $method, string $url): self
    {
        $this->request = $this->requestFactory->createRequest($method, $url);

        return $this;
    }

    public function createVerifyApiTokenRequest(string $url, string $token): RequestInterface
    {
        return $this
            ->create('GET', $url)
            ->addJwtAuthorizationHeader($token)
            ->getRequest()
        ;
    }

    public function addJwtAuthorizationHeader(string $token): self
    {
        $this->request = $this->request->withHeader(
            $this->authorizationHeaderName,
            $this->authorizationValuePrefix . $token
        );

        return $this;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
