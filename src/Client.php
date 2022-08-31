<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;

class Client
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
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

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function createUser(string $adminToken, string $email, string $password): UserCreationOutcome
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getCreateUserUrl())
            ->withAddedHeader('Authorization', $adminToken)
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query([
                'email' => $email,
                'password' => $password,
            ])))
        ;

        $response = $this->httpClient->sendRequest($request);

        $expectedContentType = 'application/json';
        $actualContentType = $response->getHeaderLine('content-type');

        if ($expectedContentType !== $actualContentType) {
            throw new InvalidResponseContentException($expectedContentType, $actualContentType, $response);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new InvalidResponseDataException('array', gettype($responseData), $response);
        }

        return new UserCreationOutcome(200 === $response->getStatusCode(), $responseData);
    }
}
