<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Authentication\Authentication;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Payload\JsonPayload;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

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

        if (409 === $response->getHttpResponse()->getStatusCode()) {
            throw new UserAlreadyExistsException($email, $response->getHttpResponse());
        }

        $responseData = $response->getData();
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
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/frontend/token/create')))
                ->withPayload(new JsonPayload([
                    'username' => $email,
                    'password' => $password,
                ]))
        );

        return $this->objectFactory->createRefreshableTokenFromArray($response->getData());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    public function listUserApiKeys(Token $token): ApiKeyCollection
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $this->createUrl('/frontend/apikey/list')))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        return $this->objectFactory->createApiKeyCollectionFromArray($response->getData());
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

        return $this->objectFactory->createRefreshableTokenFromArray($response->getData());
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

        return $this->objectFactory->createTokenFromArray($response->getData());
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
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     */
    private function makeTokenVerificationRequest(Token $token, string $url): ?User
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $url))
                ->withAuthentication(new BearerAuthentication($token->token))
        );

        if (!$response->isSuccessful()) {
            return null;
        }

        return $this->objectFactory->createUserFromArray($response->getData());
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
