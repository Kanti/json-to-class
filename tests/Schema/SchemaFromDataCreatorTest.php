<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Schema\SchemaFromDataCreator;
use Generator;
use Kanti\JsonToClass\Schema\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class SchemaFromDataCreatorTest extends TestCase
{
    /**
     * @param array<mixed>|stdClass $data
     */
    #[Test]
    #[DataProvider('dataProvider')]
    public function fromData(array|stdClass $data, Schema $expectedSchema): void
    {
        $schemaFromDataCreator = new SchemaFromDataCreator();
        $this->assertEquals($expectedSchema, $schemaFromDataCreator->fromData($data, new SaneConfig()), 'schema from array');
    }

    public static function dataProvider(): Generator
    {
        yield '__empty' => [
            'data' => [],
            'expectedSchema' => new Schema(
                listElement: new Schema(),
            ),
        ];
        yield '__property null' => [
            'data' => [
                'property' => null,
            ],
            'expectedSchema' => new Schema(
                dataKeys: [
                    'property' => new Schema(basicTypes: ['null' => true]),
                ],
            ),
        ];


        $personSchema = new Schema(
            dataKeys: [
                'name' => new Schema(basicTypes: ['string' => true]),
                'age' => new Schema(basicTypes: ['int' => true]),
            ],
        );
        $allTypesSchema = new Schema(
            dataKeys: [
                'string' => new Schema(basicTypes: ['string' => true]),
                'int' => new Schema(basicTypes: ['int' => true]),
                'null' => new Schema(basicTypes: ['null' => true]),
                'bool' => new Schema(basicTypes: ['bool' => true]),
                'float' => new Schema(basicTypes: ['float' => true]),
            ],
        );
        yield 'simple' => [
            'data' => ['name' => 'Kanti', 'age' => 30],
            'expectedSchema' => $personSchema,
        ];
        yield 'allTypesSchema' => [
            'data' => [
                'null' => null,
                'bool' => true,
                'int' => 1,
                'float' => 1.1,
                'string' => 'string',
            ],
            'expectedSchema' => $allTypesSchema,
        ];
        yield 'emptyArray' => [
            'data' => ['emptyArray' => []],
            'expectedSchema' => new Schema(
                dataKeys: [
                    'emptyArray' => new Schema(
                        listElement: new Schema(),
                    ),
                ],
            ),
        ];
        yield 'emptyStdClass' => [
            'data' => ['emptyArray' => new stdClass()],
            'expectedSchema' => new Schema(
                dataKeys: [
                    'emptyArray' => new Schema(dataKeys: []),
                ],
            ),
        ];
        yield 'rootArray' => [
            'data' => [
                ['name' => 'Kanti', 'age' => 30],
                ['name' => 'Kanti2', 'age' => 31],
            ],
            'expectedSchema' => new Schema(
                listElement: $personSchema,
            ),
        ];
        yield 'mixedTypes_missing_+_string_+_int[]_+_object' => [
            'data' => [
                new stdClass(),
                ['mixedTypes' => 'string'],
                ['mixedTypes' => [0]],
                ['mixedTypes' => ['name' => 'Kanti', 'age' => 30]],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    dataKeys: [
                        'mixedTypes' => new Schema(
                            canBeMissing: true,
                            basicTypes: ['string' => true, 'null' => true],
                            listElement: new Schema(basicTypes: ['int' => true]),
                            dataKeys: [
                                'name' => new Schema(basicTypes: ['string' => true]),
                                'age' => new Schema(basicTypes: ['int' => true]),
                            ],
                        ),
                    ],
                ),
            ),
        ];
        yield 'rootArrayInArray' => [
            'data' => [
                [
                    ['name' => 'Kanti', 'age' => 30],
                    ['name' => 'Kanti2', 'age' => 31],
                ],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    listElement: $personSchema,
                ),
            ),
        ];
        yield 'childClass' => [
            'data' => [
                'person' => ['name' => 'Kanti', 'age' => 30],
            ],
            'expectedSchema' => new Schema(
                dataKeys: [
                    'person' => $personSchema,
                ],
            ),
        ];
        yield 'rootArrayChildClass' => [
            'data' => [
                [
                    'person' => ['name' => 'Kanti', 'age' => 30],
                ],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    dataKeys: [
                        'person' => $personSchema,
                    ],
                ),
            ),
        ];
        yield 'missing basic property' => [
            'data' => [
                ['name' => 'Kanti', 'age' => 30],
                ['name' => 'Kanti'],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    dataKeys: [
                        'name' => new Schema(basicTypes: ['string' => true]),
                        'age' => new Schema(canBeMissing: true, basicTypes: ['int' => true, 'null' => true]),
                    ],
                ),
            ),
        ];
        yield 'missing child' => [
            'data' => [
                ['name' => 'Kanti', 'age' => ['range' => [30, 39]]],
                ['name' => 'Kanti'],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    dataKeys: [
                        'name' => new Schema(basicTypes: ['string' => true]),
                        'age' => new Schema(
                            canBeMissing: true,
                            basicTypes: ['null' => true],
                            dataKeys: [
                                'range' => new Schema(
                                    listElement: new Schema(basicTypes: ['int' => true]),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
        ];
        yield 'array array array Class' => [
            'data' => [
                [[[['name' => 'Kanti', 'age' => 30]]]],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    listElement: new Schema(
                        listElement: new Schema(
                            listElement: $personSchema,
                        ),
                    ),
                ),
            ),
        ];
        yield 'DeepDataStructure' => [
            'data' => [
                [
                    'age1' => 9999,
                    'name1' => 'Kanti',
                    'friends1' => [
                        [
                            'name2' => 'Andi',
                            'age2' => 0,
                            '48x48' => 'https://example.com/avatar.png',
                            'friends2' => [
                                ['name3' => 'Andi', 'age3' => 0],
                                [
                                    'name3' => 'Bernd vom Grill',
                                    'age3' => 99.3,
                                    'friends3' => [
                                        ['name4' => 'Andi', 'age4' => 0],
                                        ['name4' => 'Bernd vom Grill', 'age4' => 99.3],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'expectedSchema' => new Schema(
                listElement: new Schema(
                    dataKeys: [
                        'age1' => new Schema(basicTypes: ['int' => true]),
                        'name1' => new Schema(basicTypes: ['string' => true]),
                        'friends1' => new Schema(
                            listElement: new Schema(
                                dataKeys: [
                                    'name2' => new Schema(basicTypes: ['string' => true]),
                                    'age2' => new Schema(basicTypes: ['int' => true]),
                                    '48x48' => new Schema(basicTypes: ['string' => true]),
                                    'friends2' => new Schema(
                                        listElement: new Schema(
                                            dataKeys: [
                                                'name3' => new Schema(basicTypes: ['string' => true]),
                                                'age3' => new Schema(basicTypes: ['int' => true, 'float' => true]),
                                                'friends3' => new Schema(
                                                    canBeMissing: true,
                                                    basicTypes: ['null' => true],
                                                    listElement: new Schema(
                                                        dataKeys: [
                                                            'name4' => new Schema(basicTypes: ['string' => true]),
                                                            'age4' => new Schema(basicTypes: ['int' => true, 'float' => true],),
                                                        ],
                                                    ),
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
