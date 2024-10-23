<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaSimplification;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaSimplificationTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function simplify(Schema $schema, ?Schema $expected): void
    {
        [$schemaSimplification, $schemaToNamedSchemaConverter] = $this->getSchemaSimplification();
        $actualSchema = $schemaSimplification->simplify($schemaToNamedSchemaConverter->convert(Data::class, $schema, null));
        $this->assertEquals($expected ? ($schemaToNamedSchemaConverter)->convert(Data::class, $expected, null) : null, $actualSchema);
    }

    public static function dataProvider(): Generator
    {
        $properties = ['a' => new Schema(basicTypes: ['int' => true])];

        yield 'empty' => [
            'schema' => new Schema(),
            'expected' => null,
        ];
        yield 'empty properties' => [
            'schema' => new Schema(dataKeys: []),
            'expected' => null,
        ];
        yield 'empty listElement' => [
            'schema' => new Schema(listElement: new Schema(), dataKeys: $properties),
            'expected' => new Schema(dataKeys: $properties),
        ];
        yield 'empty listElement->properties + properties' => [
            'schema' => new Schema(listElement: new Schema(dataKeys: []), dataKeys: []),
            'expected' => null,
        ];
    }

    /**
     * @return array{SchemaSimplification, SchemaToNamedSchemaConverter}
     */
    private function getSchemaSimplification(): array
    {
        $container = new JsonToClassContainer();
        return [
            $container->get(SchemaSimplification::class),
            $container->get(SchemaToNamedSchemaConverter::class),
        ];
    }
}
