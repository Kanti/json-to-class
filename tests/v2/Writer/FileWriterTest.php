<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Writer;

use Composer\Autoload\ClassLoader;
use Generator;
use Kanti\JsonToClass\Tests\v2\_helper\FakeFileSystem;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\v2\Writer\FileWriter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{
    #[Test]
    #[TestDox('Path not found no psr4 path found in composer autoload for NotKanti\Test')]
    public function exception1(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([], []),
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->expectExceptionMessage('Path not found no psr4 path found in composer autoload for NotKanti\Test');
        $fileWriter->writeIfNeeded(['NotKanti\Test' => FakeFileSystem::CONTENT]);
    }

    /**
     * @param list<string> $alreadyWrittenFiles
     * @param list<string> $fileLocationsWrittenTo
     */
    #[Test]
    #[DataProvider('writeIfNeededDataProvider')]
    public function writeIfNeeded(
        array $classes,
        array $alreadyWrittenFiles,
        array $fileLocationsWrittenTo,
        bool $needsRestart,
    ): void {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem($alreadyWrittenFiles, $fileLocationsWrittenTo),
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->assertEquals($needsRestart, $fileWriter->writeIfNeeded($classes), 'are changes done or not');
    }

    #[Test]
    #[TestDox('Multiple possible paths found')]
    public function exception2(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');
        $classLoader->addPsr4('Kanti\\', 'fake-src2/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem(),
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->expectExceptionMessage('Multiple possible paths found');
        $fileWriter->writeIfNeeded(['Kanti\Test' => FakeFileSystem::CONTENT]);
    }

    #[Test]
    public function classLoaderFindRealFileLocation(): void
    {
        // TODO
        $this->markTestSkipped('This test is not working with infection. It will delete itself because infection will manipulate something and than the file is not found anymore.');

        // use real class loader to find the real file location
        $container = new JsonToClassContainer([
            FileSystemInterface::class => new FakeFileSystem([__FILE__ => true], [__FILE__ => true]),
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $actual = $fileWriter->writeIfNeeded([__CLASS__ => FakeFileSystem::CONTENT]);
        $this->assertFalse($actual, 'no restart needed nothing written');
    }

    public static function writeIfNeededDataProvider(): Generator
    {
        yield 'write' => [
            'classes' => ['Kanti\Test' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => [],
            'fileLocationsWrittenTo' => ['fake-src/Test.php' => true],
            'needsRestart' => false,
        ];
        yield 'write one existing + one new' => [
            'classes' => ['Kanti\Test' => FakeFileSystem::CONTENT, 'Kanti\Test2' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => ['fake-src/Test.php' => true],
            'fileLocationsWrittenTo' => ['fake-src/Test2.php' => true],
            'needsRestart' => false,
        ];
        yield 'write one new subdirectory' => [
            'classes' => ['Kanti\Test\L\L\Sub' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => ['fake-src/Test.php' => true],
            'fileLocationsWrittenTo' => ['fake-src/Test/L/L/Sub.php' => true],
            'needsRestart' => false,
        ];
        // TODO This test is not working with infection. It will delete itself because infection will manipulate something and than the file is not found anymore.
        // TODO //////////////////////////////////////////////////////////////////////////////////////
        // TODO maybe it will work if we say that this test does not cover the JsonToClassContainer???
        // TODO //////////////////////////////////////////////////////////////////////////////////////
//        $fileNameCurrentClass = 'fake-src/' . str_replace('Kanti/', '', str_replace('\\', '/', __CLASS__)) . '.php';
//        yield 'no overwrite needed so no restart needed even if the class was already loaded' => [
//            'classes' => [__CLASS__ => FakeFileSystem::CONTENT],
//            'alreadyWrittenFiles' => [$fileNameCurrentClass => true],
//            'fileLocationsWrittenTo' => [],
//            'needsRestart' => false,
//        ];
//        yield 'overwrite needed so restart needed because the class was already loaded' => [
//            'classes' => [__CLASS__ => FakeFileSystem::CONTENT],
//            'alreadyWrittenFiles' => [],
//            'fileLocationsWrittenTo' => [$fileNameCurrentClass => true],
//            'needsRestart' => true,
//        ];
    }
}
