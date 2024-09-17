<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\CodeCreator;

use Kanti\GeneratedTest\Data;
use Generator;
use Kanti\JsonToClass\v2\Attribute\Types;
use Kanti\JsonToClass\v2\CodeCreator\TypeCreator;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\Schema;
use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

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
            'expectedUses' => [
            ],
        ];
        yield 'stdClass{}' => [
            'schema' => new Schema(properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data',
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
            ],
        ];
        $classSchema = new Schema(properties: ['int' => new Schema(basicTypes: ['int' => true])]);
        yield 'Data' => [
            'schema' => $classSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data',
        ];
        yield 'list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<L>',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, properties: ['empty' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'list<L>|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<L>>>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
        yield 'list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<L>>|string>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
                ['string'],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
        $missingClassSchema = clone $classSchema;
        $missingClassSchema->canBeMissing = true;
        $missingClassSchema->basicTypes['null'] = true;
        yield 'canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
        ];
        yield 'canBeMissing list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<L>',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'canBeMissing list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedDocBlockType' => 'list<L>|Data',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
                new Literal('Data::class'),
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<L>>>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<L>>|string>',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
                ['string'],
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
        yield 'topLevel canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
        ];
        yield 'topLevel canBeMissing list<class>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<L>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
                'null',
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'topLevel canBeMissing list<class>|class|null' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema, properties: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array|null',
            'expectedDocBlockType' => 'list<L>|Data|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [new Literal('L::class')],
                new Literal('Data::class'),
                'null',
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<L>>>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
                'null',
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<L>>|string>|null',
            'expectedAttribute' => new Attribute(Types::class, [
                [[[new Literal('L::class')]]],
                ['string'],
                'null',
            ]),
            'expectedUses' => [
                'L' => 'Kanti\GeneratedTest\Data\L\L\L',
            ],
        ];
    }

    #[Test]
    #[TestDox('TypeCreator->getPhpType')]
    #[DataProvider('dataProvider')]
    public function getPhpType(Schema $schema, string $expectedPhpType, array $expectedUses = [], ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Data::class));
        $result = $typeCreator->getPhpType(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        $this->assertEquals($expectedPhpType, $result);
        $this->assertEquals([], $namespace->getUses());
    }

    #[Test]
    #[TestDox('TypeCreator->getDocBlockType')]
    #[DataProvider('dataProvider')]
    public function getDocBlockType(Schema $schema, ?string $expectedDocBlockType = null, array $expectedUses = [], ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Data::class));
        $result = $typeCreator->getDocBlockType(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        $this->assertEquals($expectedDocBlockType, $result);
        $this->assertEquals($expectedUses, $namespace->getUses());
    }

    #[Test]
    #[TestDox('TypeCreator->getAttribute')]
    #[DataProvider('dataProvider')]
    public function getAttribute(Schema $schema, ?Attribute $expectedAttribute = null, array $expectedUses = [], ...$_): void
    {
        $typeCreator = new TypeCreator();
        $namespace = new PhpNamespace(Helpers::extractNamespace(Data::class));
        $result = $typeCreator->getAttribute(NamedSchema::fromSchema(Data::class, $schema), $namespace);
        if ($expectedAttribute) {
            $this->assertNotNull($result, '🤢 Attribute is expected but not created');
            $expectedUses = [...$expectedUses, 'Types' => Types::class];
        } else {
            $this->assertNull($result, '🤨 Attribute is not expected but was created');
        }

        $this->assertEquals($expectedAttribute, $result);
        $this->assertEquals($expectedUses, $namespace->getUses());
    }
}
