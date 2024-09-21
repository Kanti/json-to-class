<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Kanti\JsonToClass\Schema\SchemaMerger;
use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaMergerTest extends TestCase
{
    #[Test]
    public function exception1(): void
    {
        $schemaMerger = new SchemaMerger();
        $this->expectExceptionMessage('Class names must be the same Kanti\A !== Kanti\B');
        $schemaMerger->merge(NamedSchema::fromSchema('Kanti\A', new Schema()), NamedSchema::fromSchema('Kanti\B', new Schema()));
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function merge(?Schema $a, ?Schema $b, ?Schema $expected): void
    {
        $schemaMerger = new SchemaMerger();
        $a = $a ? NamedSchema::fromSchema(Data::class, $a) : $a;
        $b = $b ? NamedSchema::fromSchema(Data::class, $b) : $b;
        $expected = $expected ? NamedSchema::fromSchema(Data::class, $expected) : $expected;
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
            new Schema(),
        ];
        yield 'empty + null' => [
            new Schema(),
            null,
            new Schema(),
        ];
        yield 'nothing' => [
            new Schema(),
            new Schema(),
            new Schema(),
        ];
        yield 'a' => [
            new Schema(properties: ['x' => new Schema()]),
            new Schema(),
            new Schema(properties: ['x' => new Schema()]),
        ];
        yield 'b' => [
            new Schema(),
            new Schema(properties: ['x' => new Schema()]),
            new Schema(properties: ['x' => new Schema()]),
        ];
        yield 'a and b' => [
            new Schema(properties: ['x' => new Schema()]),
            new Schema(properties: ['y' => new Schema()]),
            new Schema(properties: ['x' => new Schema(), 'y' => new Schema()]),
        ];
        yield 'a and b with same key but different types' => [
            new Schema(properties: ['x' => new Schema(basicTypes: ['string' => true])]),
            new Schema(properties: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(properties: ['x' => new Schema(basicTypes: ['string' => true, 'int' => true])]),
        ];
        yield 'a canBeMissing' => [
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(properties: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true, 'int' => true])]),
        ];
        yield 'b canBeMissing' => [
            new Schema(properties: ['x' => new Schema(basicTypes: ['int' => true])]),
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true, 'int' => true])]),
        ];
        yield 'a and b canBeMissing' => [
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
            new Schema(properties: ['x' => new Schema(canBeMissing: true, basicTypes: ['null' => true])]),
        ];
        yield 'a has properties b is a list' => [
            new Schema(properties: ['x' => new Schema()]),
            new Schema(listElement: new Schema()),
            new Schema(listElement: new Schema(), properties: ['x' => new Schema()]),
        ];
    }
}
