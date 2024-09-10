<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Code;

use Generator;
use Kanti\JsonToClass\Code\CodeGenerator;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Kanti\JsonToClass\Schema\SchemaElement;
use Nette\PhpGenerator\PsrPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CodeGeneratorTest extends TestCase
{
    public static function fromSchemaDataProvider(): Generator
    {
        $personSchemaElement = new SchemaElement(
            properties: [
                'name' => new SchemaElement(
                    basicTypes: ['string' => true],
                ),
                'age' => new SchemaElement(
                    basicTypes: ['int' => true],
                ),
            ],
        );
        yield 'person.php' => [
            $personSchemaElement,
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/Person.php.dist',
            ],
        ];
        yield 'person.php starting with a list' => [
            new SchemaElement(
                listElement: $personSchemaElement,
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/Person.php.dist',
            ],
        ];
        yield 'Names.php.dist' => [
            new SchemaElement(
                properties: [
                    'names' => new SchemaElement(
                        listElement: new SchemaElement(
                            basicTypes: ['string' => true],
                        ),
                        canBeMissing: true,
                    ),
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/Names.php.dist',
            ],
        ];
        yield 'Names.0.0.0.php.dist' => [
            new SchemaElement(
                properties: [
                    'persons' => new SchemaElement(
                        listElement: new SchemaElement(
                            listElement: new SchemaElement(
                                listElement: $personSchemaElement,
                            ),
                        ),
                    ),
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/Names.0.0.0.php.dist',
                'Kanti\JsonToClass\Dto\Data\Persons' => __DIR__ . '/../__fixtures__/0.0.0.Persons.php.dist',
            ],
        ];
        yield 'NullAndMissing.php.dist' => [
            new SchemaElement(
                properties: [
                    'missing' => new SchemaElement(
                        basicTypes: ['null' => true, 'string' => true],
                        canBeMissing: true,
                    ),
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/NullAndMissing.php.dist',
            ],
        ];

        yield 'DirectChild' => [
            new SchemaElement(
                properties: [
                    'persons' => $personSchemaElement,
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/DirectChild.php.dist',
                'Kanti\JsonToClass\Dto\Data\Persons' => __DIR__ . '/../__fixtures__/0.0.0.Persons.php.dist',
            ],
        ];
        yield 'EmptyArray.php.dist' => [
            new SchemaElement(
                properties: [
                    'emptyArray' => new SchemaElement(),
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/EmptyArray.php.dist',
            ],
        ];
        yield 'AllTheTypes.php.dist' => [
            new SchemaElement(
                properties: [
                    'property' => new SchemaElement(
                        basicTypes: ['string' => true, 'int' => true],
                        listElement: new SchemaElement(
                            listElement: new SchemaElement(
                                listElement: new SchemaElement(
                                    listElement: $personSchemaElement,
                                ),
                            ),
                        ),
                        properties: [
                            'property' => new SchemaElement(
                                basicTypes: ['string' => true, 'int' => true],
                            ),
                        ],
                        canBeMissing: true,
                    ),
                ],
            ),
            [
                'Kanti\JsonToClass\Dto\Data' => __DIR__ . '/../__fixtures__/AllTheTypes.php.dist',
            ],
        ];
    }

    #[Test]
    public function fromSchemaInvalid(): void
    {
        $codeGenerator = new CodeGenerator();
        $class = new FullyQualifiedClassName('Kanti\JsonToClass\Dto\Data');
        $invalidSchema = new SchemaElement(
            basicTypes: ['string' => true],
            properties: [
                'name' => new SchemaElement(
                    basicTypes: ['string' => true],
                ),
            ],
        );
        $this->expectExceptionMessage('Invalid schema given');
        $codeGenerator->fromSchema($class, $invalidSchema);
    }

    /**
     * @param array<string, string> $files
     */
    #[Test]
    #[DataProvider('fromSchemaDataProvider')]
    public function fromSchema(SchemaElement $schema, array $files): void
    {
        $codeGenerator = new CodeGenerator();
        $class = new FullyQualifiedClassName('Kanti\JsonToClass\Dto\Data');
        $classes = $codeGenerator->fromSchema($class, $schema);
        foreach ($classes as $key => $value) {
            $this->assertArrayHasKey($key, $files, 'generated file not expected ' . $key);
            $this->assertStringEqualsFile($files[$key], $value);
        }

        $expected = array_keys($files);
        sort($expected);
        $actual = array_keys(iterator_to_array($classes));
        sort($actual);
        $this->assertEquals($expected, $actual, 'generated files count not expected');
    }

    #[Test]
    public function fromSchemaBasicTypes(): void
    {
        $codeGenerator = new CodeGenerator();
        $class = new FullyQualifiedClassName('Kanti\JsonToClass\Dto\Data');
        $invalidSchema = new SchemaElement(
            basicTypes: ['string' => true],
        );
        $this->expectExceptionMessage('Basic types not supported at this level ' . json_encode($invalidSchema));
        $codeGenerator->fromSchema($class, $invalidSchema);
    }
}
