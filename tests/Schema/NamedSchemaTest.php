<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\JsonToClass\Helpers\SH;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NamedSchemaTest extends TestCase
{
    #[Test]
    public function exception1(): void
    {
        $this->expectExceptionMessage('Class name must contain namespace given: A');
        NamedSchema::fromSchema(SH::classString('A'), new Schema());
    }

    #[Test]
    public function getFirstNonListChild(): void
    {
        $schema = NamedSchema::fromSchema(SH::classString('Kanti\A'), new Schema(listElement: new Schema()));
        $expectedSchema = $schema->listElement;
        $this->assertSame($expectedSchema, $schema->getFirstNonListChild());
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function isOnlyAList(Schema $schema, bool $expected): void
    {
        $schema = NamedSchema::fromSchema(SH::classString('Kanti\A'), $schema);
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
            new Schema(properties: []),
            false,
        ];
        yield 'properties set' => [
            new Schema(properties: ['a' => new Schema()]),
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
}
