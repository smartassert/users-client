<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Functional\DataProvider;

use GuzzleHttp\Psr7\Response;

trait ValidJsonResponseDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function validJsonResponseDataProvider(): array
    {
        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode([
                        'key1' => 'value1',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ])
                ),
                'expected' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                    'key3' => 'value3',
                ],
            ],
        ];
    }
}
