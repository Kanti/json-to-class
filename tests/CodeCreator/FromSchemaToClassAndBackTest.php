<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use Kanti\JsonToClass\Tests\_helper\PhpFilesDriver;
use Kanti\JsonToClass\Tests\_helper\PhpFilesDto;
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
    public function test(Schema $schema, mixed ...$_): void
    {

        $container = new JsonToClassContainer();
        $wrappedSchema = new Schema(properties: ['a' => $schema]);
        $actualFiles = $container->get(CodeCreator::class)->createFiles(NamedSchema::fromSchema(Data::class, $wrappedSchema));


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
        $actualReadSchema = $container->get(SchemaFromClassCreator::class)->fromClasses(Data::class);
        $this->assertEquals($namedWrappedSchema, $actualReadSchema);
    }
}
