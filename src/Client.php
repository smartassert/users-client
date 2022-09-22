<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\UsersClient\Authentication\Authentication;
use SmartAssert\UsersClient\Authentication\BearerAuthentication;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;
use SmartAssert\UsersClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\Payload\JsonPayload;
use SmartAssert\UsersClient\Payload\UrlEncodedPayload;
use SmartAssert\UsersClient\ServiceClient\ServiceClient;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
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
        return $this->makeTokenVerificationRequest($token, $this->createUrl('/api/token/verify'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
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

        return $this->objectFactory->createUserFromArray($userData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function createFrontendToken(string $email, string $password): ?RefreshableToken
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/frontend/token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        return $this->objectFactory->createRefreshableTokenFromArray($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $this->createUrl('/frontend/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        return $this->objectFactory->createApiKeyCollectionFromArray($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
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

        return $this->objectFactory->createRefreshableTokenFromArray($responseData);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function createApiToken(string $apiKey): ?Token
    {
        $responseData = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/api/token/create')))
                ->withAuthentication(new Authentication($apiKey))
        );

        return $this->objectFactory->createTokenFromArray($responseData);
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
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
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

        return $this->objectFactory->createUserFromArray($responseData);
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
