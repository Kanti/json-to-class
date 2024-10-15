<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaSimplification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaSimplificationTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function simplify(Schema $schema, ?Schema $expected): void
    {
        $schemaSimplification = new SchemaSimplification();
        $actualSchema = $schemaSimplification->simplify(NamedSchema::fromSchema(Data::class, $schema));
        $this->assertEquals($expected ? NamedSchema::fromSchema(Data::class, $expected) : null, $actualSchema);
    }

    public static function dataProvider(): Generator
    {
        $properties = ['a' => new Schema(basicTypes: ['int' => true])];

        yield 'empty' => [
            'schema' => new Schema(),
            'expected' => null,
        ];
        yield 'empty properties' => [
            'schema' => new Schema(properties: []),
            'expected' => null,
        ];
        yield 'empty listElement' => [
            'schema' => new Schema(listElement: new Schema(), properties: $properties),
            'expected' => new Schema(properties: $properties),
        ];
        yield 'empty listElement->properties + properties' => [
            'schema' => new Schema(listElement: new Schema(properties: []), properties: []),
            'expected' => null,
        ];
    }
}
