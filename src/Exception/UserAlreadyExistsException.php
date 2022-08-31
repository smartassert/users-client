<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Exception;

use Psr\Http\Message\ResponseInterface;

class UserAlreadyExistsException extends \Exception
{
    public function __construct(
        public readonly string $email,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct(sprintf('User "%s" already exists', $this->email));
    }
}
