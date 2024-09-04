<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Code;

use Kanti\JsonToClass\Code\CodeGenerator;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Kanti\JsonToClass\Schema\SchemaElement;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class CodeGeneratorTest extends TestCase
{
    public static function fromSchemaDataProvider(): \Generator
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

    }

    #[Test]
    #[DataProvider('fromSchemaDataProvider')]
    public function fromSchema(SchemaElement $schema, array $files): void
    {
        $this->assertTrue($schema->isValid(), 'Invalid schema given');
        $printer = new PsrPrinter();
        $codeGenerator = new CodeGenerator();
        $class = new FullyQualifiedClassName('Kanti\JsonToClass\Dto\Data');
        $classes = $codeGenerator->fromSchema($class, $schema);
        foreach ($classes as $key => $value) {
            $this->assertArrayHasKey($key, $files, 'generated file not expected ' . $key);
            $this->assertStringEqualsFile($files[$key], $printer->printFile($value['phpFile']));
        }
        $this->assertEquals(
            array_keys($files),
            array_keys(iterator_to_array($classes)),
            'generated files count not expected',
        );
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
