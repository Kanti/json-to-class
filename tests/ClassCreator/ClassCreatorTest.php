<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\ClassCreator;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\ClassCreator\ClassCreator;
use Kanti\JsonToClass\ClassCreator\ShouldRestartException;
use Kanti\JsonToClass\Config\Enums\ShouldCreateClasses;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Helpers\SH;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function Safe\class_alias;

class ClassCreatorTest extends TestCase
{
    #[Test]
    #[TestDox('$className must have a namespace (must have \\ "%s" given)')]
    public function exception1(): void
    {
        $classCreator = $this->getClassCreator();

        $this->expectExceptionMessage('$className must have a namespace ("Data" dose not include \\)');
        $classCreator->createClasses(SH::classString('Data'), [], new SaneConfig());
    }

    private function getClassCreator(): ClassCreator
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);

        return $container->get(ClassCreator::class);
    }

//    #[Test]
//    #[TestDox('Schema is empty for data: %s')]
//    public function exception2(): void
//    {
//        $this->markTestSkipped('Until we have the Simplification in th ClassCreator');

//        $classCreator = $this->getClassCreator();

//        $this->expectExceptionMessage('Schema is empty for data: {"null":null}');
//        $classCreator->createClasses(Data::class, ['null' => null], new SaneConfig());
//    }

    #[Test]
    #[RunInSeparateProcess]
    #[TestDox('Class ' . Data::class . ' already exists and cannot be reloaded')]
    public function exception3(): void
    {
        $classCreator = $this->getClassCreator();
        class_alias(self::class, Data::class);

        $exceptionMessage = 'No Exception Catched';
        try {
            $classCreator->createClasses(Data::class, ['int' => 0], new SaneConfig(shouldCreateClasses: ShouldCreateClasses::YES));
        } catch (ShouldRestartException $shouldRestartException) {
            $exceptionMessage = $shouldRestartException->getMessage();
        }

        $this->assertStringContainsString('Class ' . Data::class . ' already exists and cannot be reloaded', $exceptionMessage);
        $this->assertStringContainsString(PHP_EOL . 'Please restart the application to reload the classes', $exceptionMessage);
        $this->assertStringContainsString(PHP_EOL . 'make sure you do not load the classes yourself, that would prevent the monkey patching', $exceptionMessage);
    }
}
