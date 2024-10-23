<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Features;

use Composer\Autoload\ClassLoader;
use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Kanti\JsonToClass\Schema\SchemaSimplification;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use Kanti\JsonToClass\Tests\_helper\PhpFilesDriver;
use Kanti\JsonToClass\Tests\_helper\PhpFilesDto;
use Kanti\JsonToClass\Tests\CodeCreator\TypeCreatorTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class FromSchemaToClassAndBackTest extends TestCase
{
    use MatchesSnapshots;

    #[Test]
    #[TestDox('CodeCreator->createFiles and SchemaFromClassCreator->fromClasses')]
    #[DataProviderExternal(TypeCreatorTest::class, 'dataProvider')]
    #[DataProvider('dataProvider')]
    public function test(Schema $schema, ?string $dataKey = null, mixed ...$_): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);
        $wrappedSchema = new Schema(dataKeys: ['a' => $schema]);
        $namedSchema = $container->get(SchemaToNamedSchemaConverter::class)->convert(Data::class, $wrappedSchema, null);
        $namedSchema->properties['a']->dataKey = $dataKey ?? 'a';
        $namedWrappedSchema = clone $namedSchema;
//        $namedSchema = $container->get(SchemaSimplification::class)->simplify($namedSchema);
//        if (!$namedSchema) {
//            $this->markTestSkipped('Schema is empty after simplification');
//        }

        $actualFiles = $container->get(CodeCreator::class)->createFiles($namedSchema);


        $actual = new PhpFilesDto($actualFiles, $this->dataName(), $this->providedData());
        $this->assertMatchesSnapshot($actual, new PhpFilesDriver());


        $filesWithFilenameIndex = [];
        foreach ($actualFiles as $className => $content) {
            $filename = 'fake-src/' . str_replace('\\', '/', str_replace('Kanti\\', '', $className)) . '.php';
            $filesWithFilenameIndex[$filename] = $content;
        }

        //////////////////////////////////////////////////////////////////
        // test generated code if that could be read back into a schema //
        //////////////////////////////////////////////////////////////////
        $fakeFileSystem = new FakeFileSystem($filesWithFilenameIndex);
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => $fakeFileSystem,
        ]);
        $actualReadSchema = $container->get(SchemaFromClassCreator::class)->fromClasses(Data::class);
        $this->assertEquals($namedWrappedSchema, $actualReadSchema);
        $fakeFileSystem->assertFilesWrittenTo([]);
    }

    public static function dataProvider(): Generator
    {
        yield 'starting with a number' => [
            new Schema(dataKeys: [
                '_48x48' => new Schema(basicTypes: ['string' => true]),
            ]),
        ];
    }
}
