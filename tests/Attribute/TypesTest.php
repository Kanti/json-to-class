<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Attribute;

use Kanti\JsonToClass\Attribute\Types;
use Generator;
use Kanti\JsonToClass\Dto\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function construct(array $input, array $expected): void
    {
        $this->assertEquals($expected, (new Types(...$input))->types);
    }

    public static function dataProvider(): Generator
    {
        yield 'string' => [
            'input' => ['string'],
            'expected' => [new Type('string')],
        ];
        yield '[string]' => [
            'input' => [['string']],
            'expected' => [new Type('string', 1)],
        ];
        yield '[[string]]' => [
            'input' => [[['string']]],
            'expected' => [new Type('string', 2)],
        ];
        yield '[[string]], [string]' => [
            'input' => [[['string']], ['string']],
            'expected' => [new Type('string', 2), new Type('string', 1)],
        ];
        yield 'int, null' => [
            'input' => ['int', 'null'],
            'expected' => [new Type('int'), new Type('null')],
        ];
    }
}
