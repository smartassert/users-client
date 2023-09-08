<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Authentication\Authentication;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function verifyApiToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/api/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function verifyFrontendToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/frontend/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws UserAlreadyExistsException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function createUser(string $adminToken, string $email, string $password): User
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/admin/user/create')))
                ->withAuthentication(new Authentication($adminToken))
                ->withPayload(new UrlEncodedPayload([
                    'email' => $email,
                    'password' => $password,
                ]))
        );

        if (409 === $response->getStatusCode()) {
            throw new UserAlreadyExistsException($email, $response->getHttpResponse());
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $userData = $responseDataInspector->getArray('user');

        $user = $this->createUserModel(new ArrayInspector($userData));
        if (null === $user) {
            throw InvalidModelDataException::fromJsonResponse(User::class, $response);
        }

        return $user;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function createFrontendToken(string $email, string $password): RefreshableToken
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/frontend/token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $token = $this->createRefreshableTokenModel($response);
        if (null === $token) {
            throw InvalidModelDataException::fromJsonResponse(RefreshableToken::class, $response);
        }

        return $token;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/frontend/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        /**
         * @var array<ApiKey> $apiKeys
         */
        $apiKeys = $responseDataInspector->each(
            function ($key, mixed $value): ?ApiKey {
                if (is_array($value)) {
                    $valueInspector = new ArrayInspector($value);

                    $apiKeyKey = $valueInspector->getString('key');
                    if (is_string($apiKeyKey)) {
                        return new ApiKey($valueInspector->getString('label'), $apiKeyKey);
                    }
                }

                return null;
            }
        );

        return new ApiKeyCollection($apiKeys);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     */
    public function getUserDefaultApiKey(Token $token): ?ApiKey
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->createUrl('/frontend/apikey')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $apiKeyKey = $responseDataInspector->getString('key');

        if (is_string($apiKeyKey)) {
            return new ApiKey($responseDataInspector->getString('label'), $apiKeyKey);
        }

        return null;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function refreshFrontendToken(RefreshableToken $token): ?RefreshableToken
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/frontend/token/refresh')))
                ->withPayload(new JsonPayload(['refresh_token' => $token->refreshToken]))
        );

        if (401 === $response->getStatusCode()) {
            return null;
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $token = $this->createRefreshableTokenModel($response);
        if (null === $token) {
            throw InvalidModelDataException::fromJsonResponse(RefreshableToken::class, $response);
        }

        return $this->createRefreshableTokenModel($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function createApiToken(string $apiKey): ?Token
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/api/token/create')))
                ->withAuthentication(new Authentication($apiKey))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $tokenValue = $responseDataInspector->getNonEmptyString('token');

        if (null === $tokenValue) {
            throw InvalidModelDataException::fromJsonResponse(Token::class, $response);
        }

        return new Token($tokenValue);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     */
    public function revokeFrontendRefreshToken(string $adminToken, string $userId): void
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/admin/frontend/refresh-token/revoke')))
                ->withAuthentication(new Authentication($adminToken))
                ->withPayload(new UrlEncodedPayload(['id' => $userId]))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @throws InvalidResponseDataException
     */
    private function createRefreshableTokenModel(JsonResponse $response): ?RefreshableToken
    {
        $responseDataInspector = new ArrayInspector($response->getData());

        $token = $responseDataInspector->getNonEmptyString('token');
        $refreshToken = $responseDataInspector->getNonEmptyString('refresh_token');

        return null === $token || null === $refreshToken ? null : new RefreshableToken($token, $refreshToken);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    private function makeTokenVerificationRequest(Token $token, string $url): ?User
    {
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $url))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (401 === $response->getStatusCode()) {
            return null;
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $user = $this->createUserModel(new ArrayInspector($response->getData()));
        if (null === $user) {
            throw InvalidModelDataException::fromJsonResponse(User::class, $response);
        }

        return $user;
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }

    private function createUserModel(ArrayInspector $data): ?User
    {
        $id = $data->getNonEmptyString('id');
        $userIdentifier = $data->getNonEmptyString('user-identifier');

        if (null === $id || null === $userIdentifier) {
            return null;
        }

        return new User($id, $userIdentifier);
    }
}
