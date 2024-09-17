<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Schema;

use Kanti\GeneratedTest\Data;
use Generator;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\Schema;
use Kanti\JsonToClass\v2\Schema\SchemaMerger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaMergerTest extends TestCase
{
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
    }
}
