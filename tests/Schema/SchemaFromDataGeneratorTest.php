<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\JsonToClass\Schema\SchemaElement;
use Kanti\JsonToClass\Schema\SchemaFromDataGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaFromDataGeneratorTest extends TestCase
{

    #[Test]
    #[DataProvider('provideData')]
    public function generate(array $input, SchemaElement $expectedOutput): void
    {
        $schemaFromDataGenerator = new SchemaFromDataGenerator();
        $result = $schemaFromDataGenerator->generate($input);
        $this->assertEquals($expectedOutput, $result);
    }

    public static function provideData(): Generator
    {
        $personSchema = new SchemaElement(
            properties: [
                'name' => new SchemaElement(['string' => true]),
                'age' => new SchemaElement(['int' => true]),
            ],
        );
        $allTypesSchema = new SchemaElement(
            properties: [
                'string' => new SchemaElement(['string' => true]),
                'int' => new SchemaElement(['int' => true]),
                'null' => new SchemaElement(['null' => true]),
                'bool' => new SchemaElement(['bool' => true]),
                'float' => new SchemaElement(['float' => true]),
            ],
        );
        yield 'simple' => [
            'input' => ['name' => 'Kanti', 'age' => 30],
            'expectedOutput' => $personSchema,
        ];
        yield 'allTypesSchema' => [
            'input' => [
                'null' => null,
                'bool' => true,
                'int' => 1,
                'float' => 1.1,
                'string' => 'string',
            ],
            'expectedOutput' => $allTypesSchema,
        ];
        yield 'emptyArray' => [
            'input' => ['emptyArray' => []],
            'expectedOutput' => new SchemaElement(
                properties: [
                    'emptyArray' => new SchemaElement(
                        # TODO decide if this is correct: maybe this is not a listElement but a properties element
                        listElement: new SchemaElement(),
                    ),
                ],
            ),
        ];
        yield 'rootArray' => [
            'input' => [
                ['name' => 'Kanti', 'age' => 30],
                ['name' => 'Kanti2', 'age' => 31],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: $personSchema,
            ),
        ];
        yield 'mixedTypes object + int[] + string' => [
            'input' => [
                ['mixedTypes' => 'string'],
                ['mixedTypes' => [0]],
                ['mixedTypes' => ['name' => 'Kanti', 'age' => 30]],
            ],
            'expectedOutput' => new SchemaElement(
                // TODO decide what to do with mixed types add all to the schema? @param string|int[]|MixedTypes $mixedTypes
//                listElement: $personSchema,
            ),
        ];
        yield 'rootArrayInArray' => [
            'input' => [
                [
                    ['name' => 'Kanti', 'age' => 30],
                    ['name' => 'Kanti2', 'age' => 31],
                ],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    listElement: $personSchema,
                ),
            ),
        ];
        yield 'childClass' => [
            'input' => [
                'person' => ['name' => 'Kanti', 'age' => 30],
            ],
            'expectedOutput' => new SchemaElement(
                properties: [
                    'person' => $personSchema,
                ],
            ),
        ];
        yield 'rootArrayChildClass' => [
            'input' => [
                [
                    'person' => ['name' => 'Kanti', 'age' => 30],
                ],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    properties: [
                        'person' => $personSchema,
                    ],
                ),
            ),
        ];
        yield 'missing basic property' => [
            'input' => [
                ['name' => 'Kanti', 'age' => 30],
                ['name' => 'Kanti'],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    properties: [
                        'name' => new SchemaElement(['string' => true]),
                        'age' => new SchemaElement(['int' => true], canBeMissing: true),
                    ],
                ),
            ),
        ];
        yield 'missing child' => [
            'input' => [
                ['name' => 'Kanti', 'age' => ['range' => [30, 39]]],
                ['name' => 'Kanti'],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    properties: [
                        'name' => new SchemaElement(['string' => true]),
                        'age' => new SchemaElement(
                            properties: [
                                'range' => new SchemaElement(
                                    listElement: new SchemaElement(['int' => true]),
                                ),
                            ],
                            canBeMissing: true,
                        ),
                    ],
                ),
            ),
        ];
        yield 'array array array Class' => [
            'input' => [
                [[[['name' => 'Kanti', 'age' => 30]]]],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    listElement: new SchemaElement(
                        listElement: new SchemaElement(
                            listElement: $personSchema,
                        ),
                    ),
                ),
            ),
        ];
        yield 'AAAAAAAAAAAAAAAAAAAAAA' => [
            'input' => [
                [
                    'age' => 9999,
                    'name' => 'Kanti',
                    'friends' => [
                        [
                            'name' => 'Andi',
                            'age' => 0,
                            'friends' => [
                                ['name' => 'Andi', 'age' => 0],
                                [
                                    'name' => 'Bernd vom Grill',
                                    'age' => 99.3,
                                    'friends' => [
                                        ['name' => 'Andi', 'age' => 0],
                                        ['name' => 'Bernd vom Grill', 'age' => 99.3],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expectedOutput' => new SchemaElement(
                listElement: new SchemaElement(
                    properties: [
                        'age' => new SchemaElement(['int' => true]),
                        'name' => new SchemaElement(['string' => true]),
                        'friends' => new SchemaElement(
                            listElement: new SchemaElement(
                                properties: [
                                    'name' => new SchemaElement(['string' => true]),
                                    'age' => new SchemaElement(['int' => true]),
                                    'friends' => new SchemaElement(
                                        listElement: new SchemaElement(
                                            properties: [
                                                'name' => new SchemaElement(['string' => true]),
                                                'age' => new SchemaElement(['int' => true, 'float' => true]),
                                                'friends' => new SchemaElement(
                                                    listElement: new SchemaElement(
                                                        properties: [
                                                            'name' => new SchemaElement(['string' => true]),
                                                            'age' => new SchemaElement(['int' => true, 'float' => true],
                                                            ),
                                                        ],
                                                    ),
                                                    canBeMissing: true,
                                                ),
                                            ],
                                        ),
                                    ),
                                ],
                            ),
                        ),
                    ],
                ),
            ),
        ];
    }
}
