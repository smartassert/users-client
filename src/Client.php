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
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\FrontendCredentials;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

readonly class Client implements ClientInterface
{
    public function __construct(
        private string $baseUrl,
        private ServiceClient $serviceClient,
    ) {
    }

    public function verifyApiToken(string $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/api-token/verify'));
    }

    public function verifyFrontendToken(string $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/frontend-token/verify'));
    }

    public function createUser(string $adminToken, string $email, string $password): User
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                (new Request('POST', $this->createUrl('/user/create')))
                    ->withAuthentication(new Authentication($adminToken))
                    ->withPayload(new UrlEncodedPayload([
                        'email' => $email,
                        'password' => $password,
                    ]))
            );
        } catch (NonSuccessResponseException $e) {
            if (409 === $e->getStatusCode()) {
                throw new UserAlreadyExistsException($email, $e->getHttpResponse());
            }

            throw $e;
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $userData = $responseDataInspector->getArray('user');

        $user = $this->createUserModel(new ArrayInspector($userData));
        if (null === $user) {
            throw InvalidModelDataException::fromJsonResponse(User::class, $response);
        }

        return $user;
    }

    public function createFrontendCredentials(string $email, string $password): FrontendCredentials
    {
        $response = $this->serviceClient->sendRequestForJson(
            (new Request('POST', $this->createUrl('/frontend-token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        $token = $this->createFrontendCredentialsModel($response);
        if (null === $token) {
            throw InvalidModelDataException::fromJsonResponse(FrontendCredentials::class, $response);
        }

        return $token;
    }

    public function listUserApiKeys(string $token): ApiKeyCollection
    {
        $response = $this->serviceClient->sendRequestForJson(
            (new Request('GET', $this->createUrl('/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token))
        );

        $responseDataInspector = new ArrayInspector($response->getData());

        /**
         * @var array<ApiKey> $apiKeys
         */
        $apiKeys = $responseDataInspector->each(
            function ($key, mixed $value): ?ApiKey {
                if (is_array($value)) {
                    $valueInspector = new ArrayInspector($value);

                    $apiKeyKey = $valueInspector->getNonEmptyString('key');
                    if (is_string($apiKeyKey)) {
                        return new ApiKey($valueInspector->getNonEmptyString('label'), $apiKeyKey);
                    }
                }

                return null;
            }
        );

        return new ApiKeyCollection($apiKeys);
    }

    public function getUserDefaultApiKey(string $token): ?ApiKey
    {
        $response = $this->serviceClient->sendRequestForJson(
            (new Request('GET', $this->createUrl('/apikey')))
                ->withAuthentication(new BearerAuthentication($token))
        );

        $responseDataInspector = new ArrayInspector($response->getData());

        $apiKeyKey = $responseDataInspector->getNonEmptyString('key');
        if (is_string($apiKeyKey)) {
            return new ApiKey(null, $apiKeyKey);
        }

        return null;
    }

    public function refreshFrontendCredentials(string $refreshToken): ?FrontendCredentials
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                (new Request('POST', $this->createUrl('/frontend-token/refresh')))
                    ->withPayload(new JsonPayload(['refresh_token' => $refreshToken]))
            );
        } catch (UnauthorizedException) {
            return null;
        }

        $refreshToken = $this->createFrontendCredentialsModel($response);
        if (null === $refreshToken) {
            throw InvalidModelDataException::fromJsonResponse(FrontendCredentials::class, $response);
        }

        return $this->createFrontendCredentialsModel($response);
    }

    public function createApiToken(string $apiKey): Token
    {
        $response = $this->serviceClient->sendRequestForJson(
            (new Request('POST', $this->createUrl('/api-token/create')))
                ->withAuthentication(new Authentication($apiKey))
        );

        $responseDataInspector = new ArrayInspector($response->getData());
        $tokenValue = $responseDataInspector->getNonEmptyString('token');

        if (null === $tokenValue) {
            throw InvalidModelDataException::fromJsonResponse(Token::class, $response);
        }

        return new Token($tokenValue);
    }

    public function revokeFrontendRefreshTokensForUser(string $adminToken, string $userId): void
    {
        $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/refresh-token/revoke-all-for-user')))
                ->withAuthentication(new Authentication($adminToken))
                ->withPayload(new UrlEncodedPayload(['id' => $userId]))
        );
    }

    public function revokeFrontendRefreshToken(string $token, string $refreshToken): void
    {
        $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/refresh-token/revoke')))
                ->withAuthentication(new BearerAuthentication($token))
                ->withPayload(new UrlEncodedPayload(['refresh_token' => $refreshToken]))
        );
    }

    /**
     * @throws InvalidResponseDataException
     */
    private function createFrontendCredentialsModel(JsonResponse $response): ?FrontendCredentials
    {
        $responseDataInspector = new ArrayInspector($response->getData());

        $token = $responseDataInspector->getNonEmptyString('token');
        $refreshToken = $responseDataInspector->getNonEmptyString('refresh_token');
        $apiKey = $responseDataInspector->getNonEmptyString('api_key');

        return null === $token || null === $refreshToken || null === $apiKey
            ? null
            : new FrontendCredentials($token, $refreshToken, $apiKey);
    }

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    private function makeTokenVerificationRequest(string $token, string $url): ?User
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                (new Request('GET', $url))
                    ->withAuthentication(new BearerAuthentication($token))
            );
        } catch (UnauthorizedException) {
            return null;
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
