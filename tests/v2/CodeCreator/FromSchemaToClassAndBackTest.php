<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\CodeCreator;

use Kanti\GeneratedTest\Data;
use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\Tests\v2\_helper\FakeFileSystem;
use Kanti\JsonToClass\Tests\v2\_helper\PhpFilesDriver;
use Kanti\JsonToClass\Tests\v2\_helper\PhpFilesDto;
use Kanti\JsonToClass\v2\CodeCreator\CodeCreator;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\Schema;
use Kanti\JsonToClass\v2\Schema\SchemaFromClassCreator;
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
    public function test(Schema $schema, ...$_): void
    {

        $container = new JsonToClassContainer();
        $wrappedSchema = new Schema(properties: ['a' => $schema]);
        $codeCreator = $container->get(CodeCreator::class);
        assert($codeCreator instanceof CodeCreator);
        $actualFiles = $codeCreator->createFiles(NamedSchema::fromSchema(Data::class, $wrappedSchema));


        $actual = new PhpFilesDto($actualFiles, $this->dataName(), $this->providedData());
        $this->assertMatchesSnapshot($actual, new PhpFilesDriver());

        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $filesWithFilenameIndex = [];
        foreach ($actualFiles as $className => $content) {
            $filename = 'fake-src/' . str_replace('\\', '/', str_replace('Kanti\\', '', $className)) . '.php';
            $filesWithFilenameIndex[$filename] = $content;
        }

        //////////////////////////////////////////////////////////////////
        // test generated code if that could be read back into a schema //
        //////////////////////////////////////////////////////////////////
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem($filesWithFilenameIndex),
        ]);
        $namedWrappedSchema = NamedSchema::fromSchema(Data::class, $wrappedSchema);
        $schemaFromClassCreator = $container->get(SchemaFromClassCreator::class);
        assert($schemaFromClassCreator instanceof SchemaFromClassCreator);
        $actualReadSchema = $schemaFromClassCreator->fromClasses(Data::class);
        $this->assertEquals($namedWrappedSchema, $actualReadSchema);
    }
}
