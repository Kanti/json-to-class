<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\JsonToClass\Schema\SchemaElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaElementTest extends TestCase
{

    #[Test]
    public function isValid()
    {
        $invalidSchema = new SchemaElement(
            basicTypes: ['string' => true],
            listElement: new SchemaElement(
                basicTypes: ['string' => true],
            ),
        );
        $schema = new SchemaElement(properties: [
            'name' => $invalidSchema,
        ]);
        $this->assertFalse($schema->isValid());
    }

    #[Test]
    #[DataProvider('getBasicTypesDataProvider')]
    public function getBasicTypes(SchemaElement $schema, array $basicTypes): void
    {
        $this->assertEquals($basicTypes, $schema->getBasicTypes());
    }

    public static function getBasicTypesDataProvider(): Generator
    {
        yield 'string' => [
            new SchemaElement(
                basicTypes: ['string' => true],
            ),
            ['string'],
        ];
        yield 'string + canBeMissing' => [
            new SchemaElement(
                basicTypes: ['string' => true],
                canBeMissing: true,
            ),
            ['string', 'null'],
        ];
        yield 'string|int|null|bool|float' => [
            new SchemaElement(
                basicTypes: [
                    'string' => true,
                    'int' => true,
                    'null' => true,
                    'bool' => true,
                    'float' => true,
                ],
            ),
            ['string', 'float', 'int', 'bool', 'null'],
        ];
        yield 'float|string|int|null|bool' => [
            new SchemaElement(
                basicTypes: [
                    'float' => true,
                    'string' => true,
                    'int' => true,
                    'null' => true,
                    'bool' => true,
                ],
            ),
            ['string', 'float', 'int', 'bool', 'null'],
        ];
    }
}
