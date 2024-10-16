<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Features;

use Composer\Autoload\ClassLoader;
use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Config\Enums\ShouldCreateClasses;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Converter\Converter;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function json_encode;

class FromDataToCodeToDataTest extends TestCase
{
    use MatchesSnapshots;

    #[Test]
    #[TestDox('json to class to json')]
    #[DataProvider('dataProvider')]
    public function test(string $json): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);

        /** @var Converter $converter */
        $converter = $container->get(Converter::class);
        $instance = $converter->jsonDecode(Data::class, $json, new SaneConfig(shouldCreateClasses: ShouldCreateClasses::YES));

        $this->assertInstanceOf(Data::class, $instance);
        $this->assertEquals($json, json_encode($instance));

        $jsonArray = '[' . $json . ',' . $json . ']';
        $instances = $converter->jsonDecodeList(Data::class, $jsonArray, new SaneConfig(shouldCreateClasses: ShouldCreateClasses::YES));

        $this->assertContainsOnlyInstancesOf(Data::class, $instances);
        $this->assertEquals($jsonArray, json_encode($instances));
    }

    public static function dataProvider(): Generator
    {
//        yield 'null' => [json_encode(['a' => null])]; throws: Schema is empty for data: {"a":null}
        yield 'bool' => [json_encode(['a' => true])];
        yield 'int' => [json_encode(['a' => 1])];
        yield 'float' => [json_encode(['a' => 1.0])];
        yield 'string' => [json_encode(['a' => 'string'])];
//        yield 'null[]' => [json_encode(['a' => [null]])]; throws: Schema is empty for data: {"a":[null]}
        yield 'bool[]' => [json_encode(['a' => [false]])];
        yield 'int[]' => [json_encode(['a' => [1]])];
        yield 'float[]' => [json_encode(['a' => [1.0]])];
        yield 'string[]' => [json_encode(['a' => ['string']])];
//        yield 'null[][]' => [json_encode(['a' => [[null]]])]; throws: Schema is empty for data: {"a":[[null]]}
        yield 'bool[][]' => [json_encode(['a' => [[false]]])];
        yield 'int[][]' => [json_encode(['a' => [[1]]])];
        yield 'float[][]' => [json_encode(['a' => [[1.0]]])];
        yield 'string[][]' => [json_encode(['a' => [['string']]])];
    }
}