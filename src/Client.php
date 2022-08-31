<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;

class Client
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpClientInterface $httpClient,
        private readonly Routes $routes,
    ) {
    }

    public function verifyApiToken(string $token): ?string
    {
        $request = $this->requestBuilder->createVerifyApiTokenRequest($this->routes->getVerifyApiTokenUrl(), $token);

        try {
            $response = $this->httpClient->sendRequest($request);

            if (200 !== $response->getStatusCode()) {
                return null;
            }

            return $response->getBody()->getContents();
        } catch (ClientExceptionInterface) {
            return null;
        }
    }
}
