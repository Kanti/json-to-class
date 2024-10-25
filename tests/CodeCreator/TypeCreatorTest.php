<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\CodeCreator\TypeCreator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
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
            'expectedAttributes' => [new Attribute(Types::class, [
                [],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
            ],
        ];
        yield 'stdClass{}' => [
            'schema' => new Schema(dataKeys: []),
            'expectedPhpType' => Data::class,
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'array{}|stdClass{}' => [
            'schema' => new Schema(listElement: new Schema(), dataKeys: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'array{}|Data',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [],
                new Literal('Data::class'),
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data' => Data::class,
            ],
        ];
        $classSchema = new Schema(dataKeys: ['int' => new Schema(basicTypes: ['int' => true])]);
        yield 'Data' => [
            'schema' => $classSchema,
            'expectedPhpType' => Data::class,
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<Data_>',
            'expectedDocBlockUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, dataKeys: ['empty' => new Schema(canBeMissing: true, basicTypes: ['string' => true])]),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>>',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        $missingClassSchema = clone $classSchema;
        $missingClassSchema->canBeMissing = true;
        $missingClassSchema->basicTypes['null'] = true;
        yield 'canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'canBeMissing list<class>' => [
            'schema' => new Schema(listElement: $classSchema),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<Data_>',
            'expectedDocBlockUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'canBeMissing list<class>|class' => [
            'schema' => new Schema(listElement: $classSchema, dataKeys: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>>',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'topLevel canBeMissing Data' => [
            'schema' => $missingClassSchema,
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|null',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
        ];
        yield 'topLevel canBeMissing list<class>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<Data_>|null',
            'expectedDocBlockUses' => [
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
                'null',
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'topLevel canBeMissing list<class>|class|null' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: $classSchema, dataKeys: []),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array|null',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'list<Data_>|Data|null',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
                'null',
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<Data___>>>|null',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                'null',
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];
        yield 'topLevel canBeMissing list<list<list<class>>|string>' => [
            'schema' => new Schema(canBeMissing: true, basicTypes: ['null' => true], listElement: new Schema(basicTypes: ['string' => true], listElement: new Schema(listElement: $classSchema))),
            'expectedPhpType' => 'array|null',
            'expectedDocBlockType' => 'list<list<list<Data___>>|string>|null',
            'expectedDocBlockUses' => [
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [[[new Literal('Data___::class')]]],
                ['string'],
                'null',
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data___' => 'Kanti\GeneratedTest\Data___',
            ],
        ];

        yield 'expectedDocBlockUses' => [
            'schema' => new Schema(dataKeys: ['classSchema' => $classSchema], listElement: $classSchema),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [new Attribute(Types::class, [
                [new Literal('Data_::class')],
                new Literal('Data::class'),
            ])],
            'expectedAttributesUses' => [
                'Types' => Types::class,
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];

        yield 'dataKey' => [
            'dataKey' => 'class-Schema',
            'schema' => new Schema(dataKeys: ['classSchema' => $classSchema]),
            'expectedPhpType' => Data::class,
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedAttributes' => [
                new Attribute(Key::class, ['class-Schema']),
            ],
            'expectedAttributesUses' => [
                'Key' => Key::class,
            ],
        ];

        yield 'dataKey Types' => [
            'dataKey' => 'class🌏Schema',
            'schema' => new Schema(dataKeys: ['classSchema' => $classSchema], listElement: $classSchema),
            'expectedPhpType' => 'Kanti\GeneratedTest\Data|array',
            'expectedPhpTypeUses' => [
                'Data' => Data::class,
            ],
            'expectedDocBlockType' => 'list<Data_>|Data',
            'expectedDocBlockUses' => [
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
            'expectedAttributes' => [
                new Attribute(Key::class, ['class🌏Schema']),
                new Attribute(Types::class, [
                    [new Literal('Data_::class')],
                    new Literal('Data::class'),
                ]),
            ],
            'expectedAttributesUses' => [
                'Key' => Key::class,
                'Types' => Types::class,
                'Data' => Data::class,
                'Data_' => 'Kanti\GeneratedTest\Data_',
            ],
        ];
    }

    /**
     * @param array<string, string> $expectedPhpTypeUses
     */
    #[Test]
    #[TestDox('TypeCreator->getPhpType')]
    #[DataProvider('dataProvider')]
    public function getPhpType(Schema $schema, string $expectedPhpType, array $expectedPhpTypeUses = [], mixed ...$_): void
    {
        [$typeCreator, $schemaToNamedSchemaConverter] = $this->getTypeCreator();
        $namedSchema = $schemaToNamedSchemaConverter->convert(Data::class, $schema, null);
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getPhpType($namedSchema, $namespace);
        $this->assertEquals($expectedPhpType, $result);
        $this->assertEquals($expectedPhpTypeUses, $namespace->getUses(), 'expectedDocBlockUses mismatch');
    }

    /**
     * @param array<string, string> $expectedDocBlockUses
     */
    #[Test]
    #[TestDox('TypeCreator->getDocBlockType')]
    #[DataProvider('dataProvider')]
    public function getDocBlockType(Schema $schema, ?string $expectedDocBlockType = null, array $expectedDocBlockUses = [], mixed ...$_): void
    {
        [$typeCreator, $schemaToNamedSchemaConverter] = $this->getTypeCreator();
        $namedSchema = $schemaToNamedSchemaConverter->convert(Data::class, $schema, null);
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getDocBlockType($namedSchema, $namespace);
        $this->assertEquals($expectedDocBlockType, $result);
        $this->assertEquals($expectedDocBlockUses, $namespace->getUses());
    }

    /**
     * @param list<Attribute> $expectedAttributes
     * @param array<string, string> $expectedAttributesUses
     */
    #[Test]
    #[TestDox('TypeCreator->getAttributes')]
    #[DataProvider('dataProvider')]
    public function getAttributes(Schema $schema, array $expectedAttributes = [], array $expectedAttributesUses = [], ?string $dataKey = null, mixed ...$_): void
    {
        [$typeCreator, $schemaToNamedSchemaConverter] = $this->getTypeCreator();
        $namedSchema = $schemaToNamedSchemaConverter->convert(Data::class, $schema, $dataKey);
        $namespace = new PhpNamespace(Helpers::extractNamespace(Helpers::extractNamespace(Data::class)));
        $result = $typeCreator->getAttributes('a', $namedSchema, $namespace);

        $this->assertEquals($expectedAttributes, $result);
        $this->assertEquals($expectedAttributesUses, $namespace->getUses());
    }

    /**
     * @return array{TypeCreator, SchemaToNamedSchemaConverter}
     */
    protected function getTypeCreator(): array
    {
        $container = new JsonToClassContainer([
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);
        return [$container->get(TypeCreator::class), $container->get(SchemaToNamedSchemaConverter::class)];
    }
}
