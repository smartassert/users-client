<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Authentication\Authentication;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
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
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function verifyApiToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/api/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function verifyFrontendToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/frontend/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws UserAlreadyExistsException
     * @throws NonSuccessResponseException
     */
    public function createUser(string $adminToken, string $email, string $password): ?User
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
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

        $responseDataInspector = new ArrayInspector($response->getData());
        $userData = $responseDataInspector->getArray('user');

        return $this->createUserModel(new ArrayInspector($userData));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function createFrontendToken(string $email, string $password): ?RefreshableToken
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/frontend/token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        return $this->createRefreshableTokenModel($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $this->createUrl('/frontend/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
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
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function refreshFrontendToken(RefreshableToken $token): ?RefreshableToken
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/frontend/token/refresh')))
                ->withPayload(new JsonPayload(['refresh_token' => $token->refreshToken]))
        );

        if (401 === $response->getHttpResponse()->getStatusCode()) {
            return null;
        }

        return $this->createRefreshableTokenModel($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function createApiToken(string $apiKey): ?Token
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/api/token/create')))
                ->withAuthentication(new Authentication($apiKey))
        );

        $responseDataInspector = new ArrayInspector($response->getData());
        $tokenValue = $responseDataInspector->getNonEmptyString('token');

        return null === $tokenValue ? null : new Token($tokenValue);
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function revokeFrontendRefreshToken(string $adminToken, string $userId): void
    {
        $this->serviceClient->sendRequest(
            (new Request('POST', $this->createUrl('/admin/frontend/refresh-token/revoke')))
                ->withAuthentication(new Authentication($adminToken))
                ->withPayload(new UrlEncodedPayload(['id' => $userId]))
        );
    }

    /**
     * @throws InvalidResponseContentException
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
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    private function makeTokenVerificationRequest(Token $token, string $url): ?User
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $url))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (401 === $response->getStatusCode()) {
            return null;
        }

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        return $this->createUserModel(new ArrayInspector($response->getData()));
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
        $id = $data->getString('id');
        $userIdentifier = $data->getString('user-identifier');

        return is_string($id) && is_string($userIdentifier) ? new User($id, $userIdentifier) : null;
    }
}
