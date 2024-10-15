<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter;

use Generator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Converter\ClassMapper;
use Kanti\JsonToClass\Tests\Converter\__fixture__\Children;
use Kanti\JsonToClass\Tests\Converter\__fixture__\Dto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClassMapperTest extends TestCase
{
    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<mixed>|stdClass $data
     * @param T $expected
     */
    #[Test]
    #[DataProvider('dataProvider')]
    public function map(string $className, array|stdClass $data, object $expected, Config $config = new SaneConfig()): void
    {
        $container = new JsonToClassContainer();
        $classMapper = $container->get(ClassMapper::class);
        $actual = $classMapper->map($className, $data, $config);
        $this->assertEquals($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield [
            'className' => Children::class,
            'data' => [
                'name' => 'A',
                'age' => 1,
            ],
            'expected' => Children::from(name: 'A', age: 1),
        ];
        yield [
            'className' => Children::class,
            'data' => [
                'name' => 'B',
                'age' => 2,
            ],
            'expected' => Children::from(name: 'B', age: 2),
        ];
        yield [
            'className' => Dto::class,
            'data' => [
                'name' => 'C',
                'id' => 1,
                'age' => 13.5,
                'isAdult' => false,
                'children' => [
                    [
                        'name' => 'A',
                        'age' => 1,
                    ],
                    [
                        'name' => 'B',
                        'age' => 2,
                    ],
                ],
            ],
            'expected' => Dto::from(name: 'C', id: 1, age: 13.5, children: [
                Children::from(name: 'A', age: 1),
                Children::from(name: 'B', age: 2),
            ], isAdult: false),
        ];
        yield 'can_map_with_missing_property' => [
            'className' => Dto::class,
            'data' => [
                'name' => 'C',
                'id' => 1,
                'age' => 13.5,
                'children' => [],
            ],
            'expected' => Dto::from(name: 'C', id: 1, age: 13.5, children: [], isAdult: null),
        ];
        yield 'can_map_deep' => [
            'className' => Dto::class,
            'data' => [
                'name' => 'C',
                'id' => 1,
                'age' => 13.5,
                'isAdult' => null,
                'children' => [],
                'childrenDeep' => [
                    [
                        ['name' => 'A', 'age' => 1],
                    ],
                ],
                'childrenMixedDeep' => [
                    [
                        ['name' => 'B', 'age' => 2],
                    ],
                    ['name' => 'C', 'age' => 3],
                ],
            ],
            'expected' => Dto::from(
                name: 'C',
                id: 1,
                age: 13.5,
                children: [],
                childrenDeep: [[Children::from(name: 'A', age: 1)]],
                childrenMixedDeep: [[Children::from(name: 'B', age: 2)], Children::from(name: 'C', age: 3)],
            ),
        ];
    }
}
