<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Mapper;

use Generator;
use Kanti\JsonToClass\Mapper\NameMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

class NameMapperTest extends TestCase
{
    /**
     * @param list<string> $newKeys
     * @param array<string, string> $currentDataNames
     * @param array<string, string> $expected
     */
    #[Test]
    #[DataProvider('dataProviderMap')]
    public function map(array $newKeys, array $currentDataNames, array $expected): void
    {
        $nameMapper = new NameMapper();
        $this->assertSame($expected, $nameMapper->map($newKeys, $currentDataNames));
    }

    public static function dataProviderMap(): Generator
    {
        yield 'simple' => [
            'newKeys' => ['simple'],
            'currentDataNames' => [],
            'expected' => ['simple' => 'simple'],
        ];
        yield 'aß' => [
            'newKeys' => ['aß'],
            'currentDataNames' => [],
            'expected' => ['a_' => 'aß'],
        ];
        yield 'aß and a§' => [
            'newKeys' => ['aß', 'a§'],
            'currentDataNames' => [],
            'expected' => [
                'a_' => 'a§',
                'a_2' => 'aß',
            ],
        ];
        yield 'a§ and aß' => [
            'newKeys' => ['a§', 'aß'],
            'currentDataNames' => [],
            'expected' => [
                'a_' => 'a§',
                'a_2' => 'aß',
            ],
        ];
        yield 'aß, a§ and a▣' => [
            'newKeys' => ['a▣'],
            'currentDataNames' => [
                'a_' => 'aß',
                'a_2' => 'a§',
            ],
            'expected' => [
                'a_' => 'aß',
                'a_2' => 'a§',
                'a_3' => 'a▣',
            ],
        ];
        yield 'overwrite' => [
            'newKeys' => ['a▣b', 'a_b'],
            'currentDataNames' => [],
            'expected' => ['a_b' => 'a_b', 'a_b_2' => 'a▣b'],
        ];
        yield 'overwrite reverse' => [
            'newKeys' => ['a_b', 'a▣b'],
            'currentDataNames' => [],
            'expected' => ['a_b' => 'a_b', 'a_b_2' => 'a▣b'],
        ];
        yield 'currentDataNames keep the names' => [
            'newKeys' => ['a_b'],
            'currentDataNames' => [
                'a_b' => 'a▣b',
                'a_b_2' => 'a§b',
            ],
            'expected' => [
                'a_b' => 'a▣b',
                'a_b_2' => 'a§b',
                'a_b_3' => 'a_b',
            ],
        ];
        yield '48x48' => [
            'newKeys' => ['48x48'],
            'currentDataNames' => [],
            'expected' => ['_48x48' => '48x48'],
        ];
    }

    #[Test]
    #[DataProvider('dataProviderSanitise')]
    public function sanitise(string $expected): void
    {
        $nameMapper = new NameMapper();
        $this->assertSame($expected, $nameMapper->sanitise((string)$this->dataName()));
    }

    public static function dataProviderSanitise(): Generator
    {
        yield 'a' => ['a'];
        yield '4x4' => ['_4x4'];
        yield from array_map(fn (string $char): array => [$char], NameMapper::CHARACTER_MAPPING);
        yield from array_map(fn (string $char): array => [$char], NameMapper::EMOJI_MAPPING);
        yield 'ß' => ['_'];
    }
}
