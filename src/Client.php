<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;

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
     * @throws UserAlreadyExistsException
     *
     * @return array<mixed>
     */
    public function createUser(string $adminToken, string $email, string $password): array
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

        if (409 === $response->getStatusCode()) {
            throw new UserAlreadyExistsException($email, $response);
        }

        return $this->getJsonResponseData($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     *
     * @return array<mixed>
     */
    public function createFrontendToken(string $email, string $password): array
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getCreateFrontendTokenUrl())
            ->withAddedHeader('content-type', 'application/json')
            ->withBody($this->streamFactory->createStream((string) json_encode([
                'username' => $email,
                'password' => $password,
            ])))
        ;

        return $this->getJsonResponseData($this->httpClient->sendRequest($request));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     *
     * @return array<mixed>
     */
    public function listUserApiKeys(string $token): array
    {
        $request = $this->requestFactory
            ->createRequest('GET', $this->routes->getListUserApiKeysUrl())
        ;

        $request = $this->requestBuilder->addJwtAuthorizationHeader($request, $token);

        return $this->getJsonResponseData($this->httpClient->sendRequest($request));
    }

    /**
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     *
     * @return array<mixed>
     */
    private function getJsonResponseData(ResponseInterface $response): array
    {
        $expectedContentType = 'application/json';
        $actualContentType = $response->getHeaderLine('content-type');

        if ($expectedContentType !== $actualContentType) {
            throw new InvalidResponseContentException($expectedContentType, $actualContentType, $response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (!is_array($data)) {
            throw new InvalidResponseDataException('array', gettype($data), $response);
        }

        return $data;
    }
}
