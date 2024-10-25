<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaToNamedSchemaConverterTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function convert(Schema $schema, NamedSchema $expectedSchema): void
    {
        $schemaToNamedSchemaConverter = $this->getNamedSchemaConverter();
        $actual = $schemaToNamedSchemaConverter->convert(F::classString('A\B'), $schema, null);

        $this->assertEquals($expectedSchema, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield 'empty' => [
            'schema' => new Schema(),
            'expectedSchema' => new NamedSchema(F::classString('A\B')),
        ];
        yield 'emojis' => [
            'schema' => new Schema(
                dataKeys: [
                    '🌏?' => new Schema(),
                    '⬆️?' => new Schema(),
                    '48x48' => new Schema(),
                ],
            ),
            'expectedSchema' => new NamedSchema(F::classString('A\B'), properties: [
                'EARTH_GLOBE_ASIA_AUSTRALIAQUESTION' => new NamedSchema(F::classString('A\B\EARTH_GLOBE_ASIA_AUSTRALIAQUESTION'), dataKey: '🌏?'),
                'UPWARDS_BLACK_ARROW_QUESTION' => new NamedSchema(F::classString('A\B\UPWARDS_BLACK_ARROW_QUESTION'), dataKey: '⬆️?'),
                '_48x48' => new NamedSchema(F::classString('A\B\_48x48'), dataKey: '48x48'),
            ]),
        ];
    }

    protected function getNamedSchemaConverter(): SchemaToNamedSchemaConverter
    {
        $container = new JsonToClassContainer();
        return $container->get(SchemaToNamedSchemaConverter::class);
    }
}
