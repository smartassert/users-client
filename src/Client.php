<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class Client
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly HttpClientInterface $httpClient,
        private readonly Routes $routes,
        private readonly ObjectFactory $objectFactory,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function verifyApiToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->routes->getVerifyApiTokenUrl());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function verifyFrontendToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->routes->getVerifyFrontendTokenUrl());
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function makeGetRequestWithJwtAuthorization(Token $token, string $url): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $this->addRequestJwtAuthorizationHeader($request, $token);

        return $this->httpClient->sendRequest($request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws UserAlreadyExistsException
     */
    public function createUser(string $adminToken, string $email, string $password): ?User
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

        $responseData = $this->getJsonResponseData($response);
        $userData = $responseData['user'] ?? [];
        $userData = is_array($userData) ? $userData : [];

        return $this->objectFactory->createUserFromArray($userData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function createFrontendToken(string $email, string $password): ?RefreshableToken
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getCreateFrontendTokenUrl())
            ->withAddedHeader('content-type', 'application/json')
            ->withBody($this->streamFactory->createStream((string) json_encode([
                'username' => $email,
                'password' => $password,
            ])))
        ;

        return $this->objectFactory->createRefreshableTokenFromArray(
            $this->getJsonResponseData($this->httpClient->sendRequest($request))
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $request = $this->requestFactory
            ->createRequest('GET', $this->routes->getListUserApiKeysUrl())
        ;

        $request = $this->addRequestJwtAuthorizationHeader($request, $token);
        $responseData = $this->getJsonResponseData($this->httpClient->sendRequest($request));

        return $this->objectFactory->createApiKeyCollectionFromArray($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function refreshFrontendToken(RefreshableToken $token): ?RefreshableToken
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getRefreshFrontendTokenUrl())
            ->withAddedHeader('content-type', 'application/json')
            ->withBody($this->streamFactory->createStream((string) json_encode([
                'refresh_token' => $token->refreshToken,
            ])))
        ;

        return $this->objectFactory->createRefreshableTokenFromArray(
            $this->getJsonResponseData($this->httpClient->sendRequest($request))
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function createApiToken(string $apiKey): ?Token
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getCreateApiTokenUrl())
            ->withAddedHeader('Authorization', $apiKey)
        ;

        return $this->objectFactory->createTokenFromArray(
            $this->getJsonResponseData($this->httpClient->sendRequest($request))
        );
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function revokeFrontendRefreshToken(string $adminToken, string $userId): void
    {
        $request = $this->requestFactory
            ->createRequest('POST', $this->routes->getRevokeFrontendRefreshTokenUrl())
            ->withAddedHeader('Authorization', $adminToken)
            ->withAddedHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query([
                'id' => $userId,
            ])))
        ;

        $this->httpClient->sendRequest($request);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    private function makeTokenVerificationRequest(Token $token, string $url): ?User
    {
        $response = $this->makeGetRequestWithJwtAuthorization($token, $url);
        if (200 !== $response->getStatusCode()) {
            return null;
        }

        return $this->objectFactory->createUserFromArray($this->getJsonResponseData($response));
    }

    /**
     * @return array<mixed>
     *
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
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

    private function addRequestJwtAuthorizationHeader(RequestInterface $request, Token $token): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $token->token);
    }
}
