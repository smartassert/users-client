<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class Client
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpClientInterface $httpClient,
        private readonly Routes $routes,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function verifyApiToken(string $token): ?string
    {
        $request = $this->requestFactory->createRequest('GET', $this->routes->getVerifyApiTokenUrl());
        $request = $this->requestBuilder->addJwtAuthorizationHeader($request, $token);

        $response = $this->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        return $response->getBody()->getContents();
    }
}
