<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\CodeCreator\TypeCreator;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function array_filter;

class TypeCreatorTest extends TestCase
{
    public static function dataProvider(): Generator
    {
        yield 'int' => [
            'schema' => new Schema(basicTypes: ['int' => true]),
            'expectedPhpType' => 'int',
        ];
        yield 'string|int' => [
            'schema' => new Schema(basicTypes: ['string' => true, 'int' => true]),
            'expectedPhpType' => 'string|int',
        ];
        yield 'string|int diffrent sorting' => [
            'schema' => new Schema(basicTypes: ['int' => true, 'string' => true]),
            'expectedPhpType' => 'string|int',
        ];
        yield 'array{}' => [
            'schema' => new Schema(listElement: new Schema()),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'array{}',
            'expectedAttribute' => new Attribute(Types::class, [
                [],
            ]),
        ];
        yield 'stdClass{}' => [
            'schema' => new Schema(properties: []),
            'expectedPhpType' => Data::class,
            'expectedUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'array{}|stdClass{}' => [
            'schema' => new Schema(listElement: new Schema(), properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'array{}|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'Data' => Data::class,
            ],
        ];
        $classSchema = new Schema(properties: ['int' => new Schema(basicTypes: ['int' => true])]);
        yield 'Data' => [
            'schema' => $classSchema,
            'expectedPhpType' => Data::class,
            'expectedUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<Data_>',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
            ]),
            'expectedUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, properties: ['empty' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        $missingClassSchema = clone $classSchema;
        $missingClassSchema->canBeMissing = true;
        $missingClassSchema->basicTypes['null'] = true;
        yield 'canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
            'expectedUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'canBeMissing list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<Data_>',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
            ]),
            'expectedUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'canBeMissing list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'topLevel canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
            'expectedUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'topLevel canBeMissing list<class>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<Data_>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
                'null',
            ]),
            'expectedUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'topLevel canBeMissing list<class>|class|null' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema, properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array|null',
            'expectedDocBlockType' => 'list<Data_>|Data|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
                'null',
            ]),
            'expectedUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<Data___>>>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                'null',
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
                'null',
            ]),
            'expectedUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];

        yield 'expectedUses' => [
            'schema' => new Schema(properties: ['classSchema' => $classSchema], listElement: $classSchema),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
    }

    /**
     * @param array<string, string> $expectedUses
     */
    #[Test]
    #[TestDox('TypeCreator->getPhpType')]
    #[DataProvider('dataProvider')]
    public function getPhpType(Schema $schema, string $expectedPhpType, array $expectedUses = [], mixed ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getPhpType(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        $this->assertEquals($expectedPhpType, $result);
        $expectedUses = array_filter(['Data' => $expectedUses['Data'] ?? null]); // only Data is expected (if expected)
        $this->assertEquals($expectedUses, $namespace->getUses());
    }

    /**
     * @param array<string, string> $expectedUses
     */
    #[Test]
    #[TestDox('TypeCreator->getDocBlockType')]
    #[DataProvider('dataProvider')]
    public function getDocBlockType(Schema $schema, ?string $expectedDocBlockType = null, array $expectedUses = [], mixed ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getDocBlockType(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        $this->assertEquals($expectedDocBlockType, $result);
        $this->assertEquals($expectedDocBlockType ? $expectedUses : [], $namespace->getUses());
    }

    /**
     * @param array<string, string> $expectedUses
     */
    #[Test]
    #[TestDox('TypeCreator->getAttribute')]
    #[DataProvider('dataProvider')]
    public function getAttribute(Schema $schema, ?Attribute $expectedAttribute = null, array $expectedUses = [], mixed ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getAttribute(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        if ($expectedAttribute) {
            $this->assertNotNull($result, '🤢 Attribute is expected but not created');
            $expectedUses = [...$expectedUses, 'Types' => Types::class];
        } else {
            $expectedUses = [];
            $this->assertNull($result, '🤨 Attribute is not expected but was created');
        }

        $this->assertEquals($expectedAttribute, $result);
        $this->assertEquals($expectedUses, $namespace->getUses());
    }
}
