<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\Client;

use GuzzleHttp\HandlerStack;

trait CreateDefaultHandlerStackTrait
{
    protected function createHandlerStack(): HandlerStack
    {
        return HandlerStack::create($this->mockHandler);
    }
}
