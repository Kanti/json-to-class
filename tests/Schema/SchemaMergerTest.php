<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaMerger;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaMergerTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function merge(?Schema $a, ?Schema $b, ?Schema $expected): void
    {
        [$schemaMerger, $schemaToNamedSchemaConverter] = $this->getSchemaMerger();
        $a = $a ? $schemaToNamedSchemaConverter->convert(Data::class, $a, null) : $a;
        $b = $b ? $schemaToNamedSchemaConverter->convert(Data::class, $b, null) : $b;
        $expected = $expected ? $schemaToNamedSchemaConverter->convert(Data::class, $expected, null) : $expected;
        $actual = $schemaMerger->merge($a, $b);
        $this->assertEquals($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield 'null' => [
            null,
            null,
            null,
        ];
        yield 'null + empty' => [
            null,
            new Schema(),
            new Schema(canBeMissing: true),
        ];
        yield 'empty + null' => [
            new Schema(),
            null,
            new Schema(canBeMissing: true),
        ];
        yield 'nothing' => [
            new Schema(),
            new Schema(),
            new Schema(),
        ];
        yield 'a' => [
            new Schema(dataKeys: ['x' => new Schema()]),
            new Schema(),
            new Schema(dataKeys: ['x' => new Schema()]),
        ];
        yield 'b' => [
            new Schema(),
            new Schema(dataKeys: ['x' => new Schema()]),
            new Schema(dataKeys: ['x' => new Schema()]),
        ];
        yield 'a and b' => [
            new Schema(dataKeys: ['x' => new Schema()]),
            new Schema(dataKeys: ['y' => new Schema()]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true), 'y' => new Schema(canBeMissing: true)]),
        ];
        yield 'a2 and b2' => [
            new Schema(dataKeys: ['x' => new Schema(), 'z' => new Schema()]),
            new Schema(dataKeys: ['y' => new Schema(), 'j' => new Schema()]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true), 'z' => new Schema(canBeMissing: true), 'y' => new Schema(canBeMissing: true), 'j' => new Schema(canBeMissing: true)]),
        ];
        yield 'a and b with same key but different types' => [
            new Schema(dataKeys: ['x' => new Schema(basicTypes: ['string' => true])]),
            new Schema(dataKeys: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(dataKeys: ['x' => new Schema(basicTypes: ['string' => true, 'int' => true])]),
        ];
        yield 'a canBeMissing' => [
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(dataKeys: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true, 'int' => true])]),
        ];
        yield 'b canBeMissing' => [
            new Schema(dataKeys: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true, 'int' => true])]),
        ];
        yield 'a and b canBeMissing' => [
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(dataKeys: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
        ];
        yield 'a has properties b is a list' => [
            new Schema(dataKeys: ['x' => new Schema()]),
            new Schema(listElement: new Schema()),
            new Schema(listElement: new Schema(), dataKeys: ['x' => new Schema()]),
        ];
    }

    /**
     * @return array{SchemaMerger, SchemaToNamedSchemaConverter}
     */
    protected function getSchemaMerger(): array
    {
        $container = new JsonToClassContainer();
        return [$container->get(SchemaMerger::class), $container->get(SchemaToNamedSchemaConverter::class)];
    }
}
