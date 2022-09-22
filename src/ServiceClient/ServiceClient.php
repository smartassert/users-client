<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\ServiceClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SmartAssert\UsersClient\Exception\InvalidResponseContentException;
use SmartAssert\UsersClient\Exception\InvalidResponseDataException;
use SmartAssert\UsersClient\Exception\NonSuccessResponseException;
use SmartAssert\UsersClient\Request;
use SmartAssert\UsersClient\ServiceClient\Authentication\Authentication;
use SmartAssert\UsersClient\ServiceClient\Payload\Payload;

class ServiceClient
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws NonSuccessResponseException
     */
    public function sendRequest(Request $request): ResponseInterface
    {
        $httpRequest = $this->requestFactory->createRequest(
            $request->method,
            $request->url
        );

        $authentication = $request->getAuthentication();
        if ($authentication instanceof Authentication) {
            $httpRequest = $httpRequest->withHeader('Authorization', $authentication->value);
        }

        $payload = $request->getPayload();
        if ($payload instanceof Payload) {
            $httpRequest = $httpRequest
                ->withHeader('Content-Type', $payload->contentType)
                ->withBody($this->streamFactory->createStream($payload->data))
            ;
        }

        $response = $this->httpClient->sendRequest($httpRequest);
        if ($response->getStatusCode() >= 300) {
            throw new NonSuccessResponseException($response);
        }

        return $response;
    }

    /**
     * @return array<mixed>
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     */
    public function sendRequestForJsonEncodedData(Request $request): array
    {
        $response = $this->sendRequest($request);

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
