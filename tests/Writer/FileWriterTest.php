<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Writer;

use ArrayObject;
use Composer\Autoload\ClassLoader;
use Exception;
use Generator;
use Kanti\JsonToClass\ClassCreator\ShouldRestartException;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use Kanti\JsonToClass\Writer\FileWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function str_replace;

#[UsesClass(JsonToClassContainer::class)]
#[UsesClass(ClassLoader::class)]
#[CoversClass(F::class)]
#[CoversClass(FileWriter::class)]
#[CoversClass(ClassLocator::class)]
#[CoversClass(DevelopmentCodeCreator::class)]
class FileWriterTest extends TestCase
{
    #[Test]
    #[TestDox('Path not found no psr4 path found in composer autoload for NotKanti\Test')]
    public function exception1(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $fakeFileSystem = new FakeFileSystem([]);
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->expectExceptionMessage('Path not found no psr4 path found in composer autoload for NotKanti\Test');
        $fileWriter->writeIfNeeded([F::classString('NotKanti\Test') => FakeFileSystem::CONTENT]);
        $fakeFileSystem->assertFilesWrittenTo([]);
    }

    /**
     * @param array<class-string, string> $classes
     * @param array<string, string|true> $alreadyWrittenFiles
     * @param array<string, string|true> $fileLocationsWrittenTo
     */
    #[Test]
    #[DataProvider('writeIfNeededDataProvider')]
    public function writeIfNeeded(
        array $classes,
        array $alreadyWrittenFiles,
        array $fileLocationsWrittenTo,
    ): void {
        $triedLoadingClasses = FileWriterTest::triedLoadingClasses();

        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $fakeFileSystem = new FakeFileSystem($alreadyWrittenFiles);
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->assertEquals(array_keys($fileLocationsWrittenTo), $fileWriter->writeIfNeeded($classes), 'are changes done and class was loaded already');
        $fakeFileSystem->assertFilesWrittenTo($fileLocationsWrittenTo);
        foreach (array_keys($classes) as $class) {
            $this->assertArrayNotHasKey($class, $triedLoadingClasses, 'class should not be loaded after writing');
        }
    }

    #[Test]
    #[TestDox('Multiple possible paths found')]
    public function exception2(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');
        $classLoader->addPsr4('Kanti\\', 'fake-src2/');

        $fakeFileSystem = new FakeFileSystem();
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $this->expectExceptionMessage('Multiple possible paths found');
        $fileWriter->writeIfNeeded([F::classString('Kanti\Test') => FakeFileSystem::CONTENT]);
        $fakeFileSystem->assertFilesWrittenTo([]);
    }

    #[Test]
    #[TestDox('overwrite needed so restart needed because the class was already loaded')]
    public function exception3(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $fakeFileSystem = new FakeFileSystem([]);
        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $fileWriter = $container->get(FileWriter::class);

        $exceptionOrNull = null;
        try {
            $fileWriter->writeIfNeeded([
                self::class => FakeFileSystem::CONTENT,
                FileWriter::class => FakeFileSystem::CONTENT,
            ]);
        } catch (ShouldRestartException $shouldRestartException) {
            $exceptionOrNull = $shouldRestartException;
        }

        $this->assertInstanceOf(ShouldRestartException::class, $exceptionOrNull);
        $this->assertEquals(<<<EOF
Class Kanti\JsonToClass\Tests\Writer\FileWriterTest, Kanti\JsonToClass\Writer\FileWriter already exists and cannot be reloaded
Please restart the application to reload the classes
make sure you do not load the classes yourself, that would prevent the monkey patching
EOF
, $exceptionOrNull->getMessage());
    }

    #[Test]
    public function classLoaderFindRealFileLocation(): void
    {
        // use real class loader to find the real file location
        $fakeFileSystem = new FakeFileSystem([__FILE__ => true]);
        $container = new JsonToClassContainer([
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $fileWriter = $container->get(FileWriter::class);
        $actual = $fileWriter->writeIfNeeded([self::class => FakeFileSystem::CONTENT]);
        $this->assertEquals([], $actual, 'no restart needed nothing written');
        $fakeFileSystem->assertFilesWrittenTo([]);
    }

    #[Test]
    #[RunInSeparateProcess]
    public function testNoClassLoading(): void
    {
        // use real class loader to find the real file location
        $fakeFileSystem = new FakeFileSystem([__FILE__ => true]);
        $container = new JsonToClassContainer([
            FileSystemInterface::class => $fakeFileSystem,
        ]);

        $actual = $container->get(FileWriter::class)->writeIfNeeded([self::class => FakeFileSystem::CONTENT]);
        $this->assertEquals([], $actual, 'no restart needed nothing written');
        $fakeFileSystem->assertFilesWrittenTo([]);
    }

    public static function writeIfNeededDataProvider(): Generator
    {
        yield 'write' => [
            'classes' => ['Kanti\Test' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => [],
            'fileLocationsWrittenTo' => ['fake-src/Test.php' => true],
        ];
        yield 'write one existing + one new' => [
            'classes' => ['Kanti\Test' => FakeFileSystem::CONTENT, 'Kanti\Test2' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => ['fake-src/Test.php' => true],
            'fileLocationsWrittenTo' => ['fake-src/Test2.php' => true],
        ];
        yield 'write one new subdirectory' => [
            'classes' => ['Kanti\Test__\Sub' => FakeFileSystem::CONTENT, 'Kanti\Test__\Sub2' => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => ['fake-src/Test.php' => true],
            'fileLocationsWrittenTo' => ['fake-src/Test__/Sub.php' => true, 'fake-src/Test__/Sub2.php' => true],
        ];
        $fileNameCurrentClass = 'fake-src/' . str_replace('Kanti/', '', str_replace('\\', '/', self::class)) . '.php';
        yield 'no overwrite needed so no restart needed even if the class was already loaded' => [
            'classes' => [self::class => FakeFileSystem::CONTENT],
            'alreadyWrittenFiles' => [$fileNameCurrentClass => true],
            'fileLocationsWrittenTo' => [],
        ];
    }

    /**
     * @return ArrayObject<int|string, mixed>
     */
    public static function triedLoadingClasses(): ArrayObject
    {
        /** @var ArrayObject<int|string, mixed> $called */
        $called = new ArrayObject();
        spl_autoload_register(static function (string $class) use (&$called): void {
            $called[$class] = true;
        });
        return $called;
    }
}
