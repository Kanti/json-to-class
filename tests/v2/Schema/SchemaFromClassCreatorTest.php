<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Schema;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Code\FileWriter;
use Kanti\JsonToClass\v2\CodeCreator\CodeCreator;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystem;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\Schema;
use Kanti\JsonToClass\v2\Schema\SchemaFromClassCreator;
use PHPUnit\Framework\Attributes\DataProvider;
//use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaFromClassCreatorTesat extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function fromClasses(string $directory, Schema $inputSchema, Schema $expectedSchema, bool $rebuild = false): void
    {
        $rootDirectory = __DIR__ . '/__fixtures__/' . $directory;

        $inputSchema = NamedSchema::fromSchema(Data::class, $inputSchema);
        $expectedSchema = NamedSchema::fromSchema(Data::class, $expectedSchema);

        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\GeneratedTest\\', $rootDirectory);

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FileSystem($rootDirectory),
        ]);

        if ($rebuild) {
            $files = $container->get(CodeCreator::class)->createFiles($inputSchema);
            $container->get(FileWriter::class)->writeIfNeeded($files);
        }

        $creator = $container->get(SchemaFromClassCreator::class);
        assert($creator instanceof SchemaFromClassCreator);
        $actual = $creator->fromClasses($inputSchema);
        self::assertEquals($expectedSchema, $actual);
    }

    public static function dataProvider(): \Generator
    {
        $abcSchema = new NamedSchema(
            className: Data::class,
            properties: [
                'a' => new NamedSchema(
                    className: Data\A::class,
                    properties: [
                        'b' => new NamedSchema(className: Data\A\B::class, basicTypes: ['string' => true]),
                        'c' => new NamedSchema(className: Data\A\C::class, basicTypes: ['string' => true]),
                    ],
                ),
            ],
        );
        yield 'empty' => [
            'directory' => 'empty/',
            'expectedSchema' => new NamedSchema(Data::class),
        ];
        yield 'a_b_c' => [
            'directory' => 'a_b_c/',
            'expectedSchema' => $abcSchema,
        ];
    }
}
