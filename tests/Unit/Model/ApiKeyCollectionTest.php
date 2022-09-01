<?php

declare(strict_types=1);

namespace SmartAssert\UsersClient\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\ApiKeyCollection;

class ApiKeyCollectionTest extends TestCase
{
    /**
     * @dataProvider getDefaultDataProvider
     */
    public function testGetDefault(ApiKeyCollection $collection, ?ApiKey $expected): void
    {
        self::assertSame($expected, $collection->getDefault());
    }

    /**
     * @return array<mixed>
     */
    public function getDefaultDataProvider(): array
    {
        $defaultApiKey = new ApiKey(null, 'default key');
        $duplicateDefaultApiKey = new ApiKey(null, 'duplicate default key');

        return [
            'empty' => [
                'collection' => new ApiKeyCollection([]),
                'expected' => null,
            ],
            'single item collection, no default' => [
                'collection' => new ApiKeyCollection([
                    new ApiKey('label', 'key'),
                ]),
                'expected' => null,
            ],
            'single item collection, is default' => [
                'collection' => new ApiKeyCollection([
                    $defaultApiKey,
                ]),
                'expected' => $defaultApiKey,
            ],
            'multiple item collection, no default' => [
                'collection' => new ApiKeyCollection([
                    new ApiKey('label 1', 'key 1'),
                    new ApiKey('label 2', 'key 2'),
                    new ApiKey('label 3', 'key 3'),
                ]),
                'expected' => null,
            ],
            'multiple item collection, has default' => [
                'collection' => new ApiKeyCollection([
                    new ApiKey('label 1', 'key 1'),
                    new ApiKey('label 2', 'key 2'),
                    $defaultApiKey,
                    new ApiKey('label 3', 'key 3'),
                ]),
                'expected' => $defaultApiKey,
            ],
            'multiple item collection, has multiple defaults, first found is selected (1)' => [
                'collection' => new ApiKeyCollection([
                    new ApiKey('label 1', 'key 1'),
                    new ApiKey('label 2', 'key 2'),
                    $defaultApiKey,
                    new ApiKey('label 3', 'key 3'),
                    $duplicateDefaultApiKey,
                ]),
                'expected' => $defaultApiKey,
            ],
            'multiple item collection, has multiple defaults, first found is selected (2)' => [
                'collection' => new ApiKeyCollection([
                    new ApiKey('label 1', 'key 1'),
                    new ApiKey('label 2', 'key 2'),
                    $duplicateDefaultApiKey,
                    new ApiKey('label 3', 'key 3'),
                    $defaultApiKey,
                ]),
                'expected' => $duplicateDefaultApiKey,
            ],
        ];
    }
}
