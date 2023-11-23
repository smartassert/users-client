<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\UsersClient\Exception\UserAlreadyExistsException;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;
use SmartAssert\UsersClient\Model\RefreshableToken;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;

interface ClientInterface
{
    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function verifyApiToken(string $token): ?User;

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function verifyFrontendToken(string $token): ?User;

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws UserAlreadyExistsException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     * @throws NonSuccessResponseException
     */
    public function createUser(string $adminToken, string $email, string $password): User;

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function createFrontendToken(string $email, string $password): RefreshableToken;

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function listUserApiKeys(string $token): ApiKeyCollection;

    /**
     * @param non-empty-string $token
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function getUserDefaultApiKey(string $token): ?ApiKey;

    /**
     * @param non-empty-string $refreshToken
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     */
    public function refreshFrontendToken(string $refreshToken): ?RefreshableToken;

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function createApiToken(string $apiKey): Token;

    /**
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function revokeFrontendRefreshTokensForUser(string $adminToken, string $userId): void;

    /**
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function revokeFrontendRefreshToken(string $token, string $refreshToken): void;
}
