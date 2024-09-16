<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Code;

use Composer\Autoload\ClassLoader;
use Generator;
use Kanti\JsonToClass\Abstraction\FakeFileSystem;
use Kanti\JsonToClass\Code\Classes;
use Kanti\JsonToClass\Code\FileWriter;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Kanti\JsonToClass\Tests\v2\_helper\FakeFileSystem as FakeFileSystemAlias;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FileWriterTest extends TestCase
{

    protected static function getClasses(string ...$classNames): Classes
    {
        $classes = new Classes();
        foreach ($classNames as $className) {
            $classes->addClass(new FullyQualifiedClassName($className), FakeFileSystemAlias::CONTENT);
        }

        return $classes;
    }

    public static function writeIfNeededDataProvider(): Generator
    {
        yield 'write' => [
            'classes' => self::getClasses('Kanti\Test'),
            'alreadyWrittenFiles' => [],
            'fileLocationsWrittenTo' => ['src/Test.php'],
            'needsRestart' => false,
        ];
        yield 'write one existing + one new' => [
            'classes' => self::getClasses('Kanti\Test', 'Kanti\Test2'),
            'alreadyWrittenFiles' => ['src/Test.php'],
            'fileLocationsWrittenTo' => ['src/Test2.php'],
            'needsRestart' => false,
        ];
    }

    /**
     * @param list<string> $alreadyWrittenFiles
     * @param list<string> $fileLocationsWrittenTo
     */
    #[Test]
    #[DataProvider('writeIfNeededDataProvider')]
    public function writeIfNeeded(Classes $classes, array $alreadyWrittenFiles, array $fileLocationsWrittenTo, bool $needsRestart): void
    {

        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'src/');

        $fileWriter = new FileWriter(
            classLoader: $classLoader,
            fileSystem: new FakeFileSystem($alreadyWrittenFiles, $fileLocationsWrittenTo),
        );

        $this->assertEquals($needsRestart, $fileWriter->writeIfNeeded($classes), 'are changes done or not');
    }
}
