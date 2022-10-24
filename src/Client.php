<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Authentication\Authentication;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidObjectDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\ObjectFactory\ObjectFactory;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
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
        protected readonly ObjectFactory $objectFactory,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws InvalidObjectDataException
     */
    public function verifyApiToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/api/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws InvalidObjectDataException
     */
    public function verifyFrontendToken(Token $token): ?User
    {
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/frontend/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws UserAlreadyExistsException
     * @throws InvalidObjectDataException
     */
    public function createUser(string $adminToken, string $email, string $password): User
    {
        try {
            $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
                (new Request('POST', $this->createUrl('/admin/user/create')))
                    ->withAuthentication(new Authentication($adminToken))
                    ->withPayload(new UrlEncodedPayload([
                        'email' => $email,
                        'password' => $password,
                    ]))
            );
        } catch (NonSuccessResponseException $nonSuccessResponseException) {
            if (409 === $nonSuccessResponseException->getCode()) {
                throw new UserAlreadyExistsException($email, $nonSuccessResponseException->response);
            }

            throw $nonSuccessResponseException;
        }

        $userData = $responseData['user'] ?? [];
        $userData = is_array($userData) ? $userData : [];

        return $this->createUserModel($userData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidObjectDataException
     */
    public function createFrontendToken(string $email, string $password): RefreshableToken
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/frontend/token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        return $this->createRefreshableTokenModel($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidObjectDataException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $this->createUrl('/frontend/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        $apiKeys = [];

        foreach ($responseData as $apiKeyData) {
            if (is_array($apiKeyData)) {
                $apiKeys[] = $this->createApiKeyModel($apiKeyData);
            }
        }

        return new ApiKeyCollection($apiKeys);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidObjectDataException
     */
    public function refreshFrontendToken(RefreshableToken $token): ?RefreshableToken
    {
        try {
            $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
                (new Request('POST', $this->createUrl('/frontend/token/refresh')))
                    ->withPayload(new JsonPayload(['refresh_token' => $token->refreshToken]))
            );
        } catch (NonSuccessResponseException $nonSuccessResponseException) {
            if (401 === $nonSuccessResponseException->getCode()) {
                return null;
            }

            throw $nonSuccessResponseException;
        }

        return $this->createRefreshableTokenModel($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidObjectDataException
     */
    public function createApiToken(string $apiKey): ?Token
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/api/token/create')))
                ->withAuthentication(new Authentication($apiKey))
        );

        return $this->createTokenModel($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
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
     * @param array<mixed> $data
     *
     * @throws InvalidObjectDataException
     */
    private function createUserModel(array $data): User
    {
        $objectDefinition = new UserDefinition();

        $user = $this->objectFactory->create($objectDefinition, $data);

        if (!$user instanceof User) {
            throw new InvalidObjectDataException($data, $objectDefinition);
        }

        return $user;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws InvalidObjectDataException
     */
    private function createRefreshableTokenModel(array $data): RefreshableToken
    {
        $objectDefinition = new RefreshableTokenDefinition();

        $refreshableToken = $this->objectFactory->create($objectDefinition, $data);

        if (!$refreshableToken instanceof RefreshableToken) {
            throw new InvalidObjectDataException($data, $objectDefinition);
        }

        return $refreshableToken;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws InvalidObjectDataException
     */
    private function createApiKeyModel(array $data): ApiKey
    {
        $objectDefinition = new ApiKeyDefinition();

        $apiKey = $this->objectFactory->create($objectDefinition, $data);

        if (!$apiKey instanceof ApiKey) {
            throw new InvalidObjectDataException($data, $objectDefinition);
        }

        return $apiKey;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws InvalidObjectDataException
     */
    private function createTokenModel(array $data): Token
    {
        $objectDefinition = new TokenDefinition();

        $token = $this->objectFactory->create($objectDefinition, $data);

        if (!$token instanceof Token) {
            throw new InvalidObjectDataException($data, $objectDefinition);
        }

        return $token;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws InvalidObjectDataException
     */
    private function makeTokenVerificationRequest(Token $token, string $url): ?User
    {
        try {
            $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
                (new Request('GET', $url))
                    ->withAuthentication(new BearerAuthentication($token->token))
            );
        } catch (NonSuccessResponseException) {
            return null;
        }

        return $this->createUserModel($responseData);
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
}
