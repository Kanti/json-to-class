<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests;

use Generator;
use Kanti\JsonToClass\DevelopmentConverter;
use Kanti\JsonToClass\Tests\GeneratedFixture\Generated;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[RunClassInSeparateProcess]
class DevelopmentConverterTest extends TestCase
{
    protected function setUp(): void
    {
        shell_exec('rm -rf tests/GeneratedFixture/*');
    }

    protected function tearDown(): void
    {
        shell_exec('rm -rf tests/GeneratedFixture/*');
    }

    public static function dataProvider(): Generator
    {
        yield 'simple' => [
            'data' => [
                'null' => null,
                'bool' => true,
                'int' => 1,
                'float' => 1.1,
                'string' => 'string',
                'emptyArray' => [],
                'arrayNull' => [null],
                'arrayBool' => [true],
                'arrayInt' => [1],
                'arrayFloat' => [1.1],
                'arrayString' => ['string'],
                'arrayEmptyArray' => [[]],
                'record' => [
                    'null' => null,
                    'bool' => true,
                    'int' => 1,
                    'float' => 1.1,
                    'string' => 'string',
                    'emptyArray' => [],
                ],
                'levelsDeep10' => [[[[[[[[[[10]]]]]]]]]],
            ],
        ];
        yield 'diffrentTypesSameProperty' => [
            'data' => [
                [/* property missing */],
                ['property' => null],
                ['property' => true],
                ['property' => 1],
                ['property' => 1.1],
                ['property' => 'string'],
                ['property' => []],
                ['property' => [null]],
                ['property' => [true]],
                ['property' => [1]],
                ['property' => [1.1]],
                ['property' => ['string']],
                ['property' => [[]]],
                ['property' => ['property' => null]],
                ['property' => ['property' => true]],
                ['property' => ['property' => 1]],
                ['property' => ['property' => 1.1]],
                ['property' => ['property' => 'string']],
                ['property' => ['property' => []]],
            ],
        ];

        // wired characters as properties (Should this be supported?)
//        yield 'wired characters as properties abc dfg' => ['data' => ['abc dfg' => 1]];
//        yield 'wired characters as properties 1' => ['data' => ['1' => 1]];
//        yield 'wired characters as properties 1a' => ['data' => ['1a' => '1a']];
//        yield 'wired characters as properties 🏎️' => ['data' => ['🏎️' => '🐌']];
//        yield 'wired characters as properties -' => ['data' => ['-' => '-']];
//        yield 'wired characters as properties \n' => ['data' => ['\n' => '\n']];
//        yield 'wired characters as properties &' => ['data' => ['&' => '&']];
//        yield 'wired characters as properties +' => ['data' => ['+' => '+']];
//        yield 'wired characters as properties ¯\_(ツ)_/¯' => ['data' => ['¯\_(ツ)_/¯' => '+']];
//        yield 'wired characters as properties o(*≧▽≦)ツ┏━┓' => ['data' => ['o(*≧▽≦)ツ┏━┓' => '+']];
    }

    /**
     * @param array<mixed> $data
     */
    #[Test]
    #[DataProvider('dataProvider')]
    public function convert(array $data): void
    {
        if (array_is_list($data)) {
            $this->markTestSkipped('This test is not for list');
        }

        $converter = new DevelopmentConverter();
        $result = $converter->convert(Generated::class, $data);
        $this->assertInstanceOf(Generated::class, $result);
        $this->assertEquals(
            json_encode($data),
            json_encode($result),
            'data->class->json should be the same as data->json',
        );
    }

    /**
     * @param array<mixed> $data
     */
    #[Test]
    #[DataProvider('dataProvider')]
    public function convertList(array $data): void
    {
        $converter = new DevelopmentConverter();
        $result = $converter->convertList(Generated::class, [$data, $data]);
        $this->assertContainsOnlyInstancesOf(Generated::class, $result);
        $this->assertEquals(
            json_encode([$data, $data]),
            json_encode($result),
            'data->class->json should be the same as data->json',
        );
    }
}
