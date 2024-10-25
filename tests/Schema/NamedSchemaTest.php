<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NamedSchemaTest extends TestCase
{
    #[Test]
    public function exception1(): void
    {
        $this->expectExceptionMessage('Class name must contain namespace given: A');
        $this->getNamedSchemaConverter()->convert(F::classString('A'), (new Schema()), null);
    }

    #[Test]
    public function getFirstNonListChild(): void
    {
        $schema = $this->getNamedSchemaConverter()->convert(F::classString('Kanti\A'), (new Schema(listElement: new Schema())), null);
        $expectedSchema = $schema->listElement;
        $this->assertSame($expectedSchema, $schema->getFirstNonListChild());
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function isOnlyAList(Schema $schema, bool $expected): void
    {
        $schema = $this->getNamedSchemaConverter()->convert(F::classString('Kanti\A'), $schema, null);
        $this->assertEquals($expected, $schema->isOnlyAList());
    }

    public static function dataProvider(): Generator
    {
        yield 'empty' => [
            new Schema(),
            false,
        ];
        yield 'basicTypes' => [
            new Schema(basicTypes: ['int' => true]),
            false,
        ];
        yield 'listElement' => [
            new Schema(listElement: new Schema()),
            true,
        ];
        yield 'properties empty' => [
            new Schema(dataKeys: []),
            false,
        ];
        yield 'properties set' => [
            new Schema(dataKeys: ['a' => new Schema()]),
            false,
        ];
        yield 'canBeMissing' => [
            new Schema(canBeMissing: true),
            false,
        ];
        yield 'canBeMissing + listElement' => [
            new Schema(canBeMissing: true, listElement: new Schema()),
            false,
        ];
    }

    protected function getNamedSchemaConverter(): SchemaToNamedSchemaConverter
    {
        $container = new JsonToClassContainer();
        return $container->get(SchemaToNamedSchemaConverter::class);
    }
}
