<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Cache\RuntimeCache;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Dto\DevelopmentFakeClassInterface;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\Schema;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use Kanti\JsonToClass\Tests\_helper\FakeFileSystem;
use Kanti\JsonToClass\Tests\Writer\FileWriterTest;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_column;
use function array_keys;
use function assert;
use function class_exists;
use function Safe\class_alias;

class DevelopmentCodeCreatorTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    #[TestDox('Class ' . Data::class . ' already exists and is not a ' . DevelopmentFakeClassInterface::class)]
    public function exception1(): void
    {
        [$developmentCodeCreator, $schemaToNamedSchemaConverter] = $this->getDevelopmentCodeCreator();
        class_alias(self::class, Data::class);
        $namedSchema =  $schemaToNamedSchemaConverter->convert(Data::class, (new Schema(dataKeys: ['a' => new Schema(basicTypes: ['int' => true])])), null);

        $this->expectExceptionMessage('Class ' . Data::class . ' already exists and is not a ' . DevelopmentFakeClassInterface::class);
        $developmentCodeCreator->createOrUpdateDevelopmentClasses($namedSchema);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[TestDox('Expected ClassType, got Nette\PhpGenerator\TraitType for class Kanti\TraitA')]
    public function exception2(): void
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([
                'fake-src/TraitA.php' => <<<'EOF'
<?php
namespace Kanti;
trait TraitA {}
EOF

            ]),
        ]);
        $developmentCodeCreator = $container->get(DevelopmentCodeCreator::class);

        $namedSchema = new NamedSchema(F::classString('Kanti\TraitA'), properties: ['a' => new NamedSchema(F::classString('A'))]);

        $this->expectExceptionMessage('Expected ClassType, got Nette\PhpGenerator\TraitType for class Kanti\TraitA');
        $developmentCodeCreator->createOrUpdateDevelopmentClasses($namedSchema);
    }

    #[Test]
    public function createDevelopmentClasses(): void
    {
        $triedLoadingClasses = FileWriterTest::triedLoadingClasses();

        $this->assertFalse(DevelopmentCodeCreator::isDevelopmentDto(F::classString('FakeClass')), 'Class should not be a DataTrait');
        $this->assertArrayNotHasKey('FakeClass', $triedLoadingClasses, 'Class should not have been autoloaded');

        $schema = new Schema(
            listElement: new Schema(
                dataKeys: [
                    'int' => new Schema(basicTypes: ['int' => true]),
                ],
            ),
            dataKeys: [
                'expand' => new Schema(listElement: new Schema(basicTypes: ['string' => true])),
                'fields' => new Schema(dataKeys: [
                    'int' => new Schema(basicTypes: ['int' => true]),
                ]),
                'id' => new Schema(basicTypes: ['string' => true]),
                'key' => new Schema(basicTypes: ['string' => true]),
                'self' => new Schema(basicTypes: ['string' => true]),
            ],
        );
        [$developmentCodeCreator, $schemaToNamedSchemaConverter] = $this->getDevelopmentCodeCreator();
        $namedSchema = $schemaToNamedSchemaConverter->convert(Data::class, $schema, null);

        $this->assertFalse(class_exists($namedSchema->className, false), sprintf('Class %s should not exist', $namedSchema->className));

        $developmentCodeCreator->createOrUpdateDevelopmentClasses($namedSchema);

        $this->assertTrue(class_exists($namedSchema->className, false), sprintf('Class %s should now exist', $namedSchema->className));
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->className), 'Class should be a DataTrait');
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->listElement->className), 'Class should be a DataTrait');
        $this->assertTrue(DevelopmentCodeCreator::isDevelopmentDto($namedSchema->properties['fields']->className), 'Class should be a DataTrait');

        $cache = (new ReflectionClass($developmentCodeCreator))->getProperty('cache')->getValue($developmentCodeCreator);
        assert($cache instanceof RuntimeCache);
        $classProperties = $cache->getClassProperties($namedSchema->className);
        $this->assertEquals(array_keys($schema->dataKeys), array_column($classProperties, 'name'), 'Class parameters should match');

        // can be called again:
        $developmentCodeCreator->createOrUpdateDevelopmentClasses($namedSchema);
        $this->assertArrayNotHasKey($namedSchema->className, $triedLoadingClasses, 'Class should not have been autoloaded');
    }

    /**
     * @return array{DevelopmentCodeCreator, SchemaToNamedSchemaConverter}
     */
    protected function getDevelopmentCodeCreator(): array
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'fake-src/');

        $container = new JsonToClassContainer([
            ClassLoader::class => $classLoader,
            FileSystemInterface::class => new FakeFileSystem([]),
        ]);
        return [
            $container->get(DevelopmentCodeCreator::class),
            $container->get(SchemaToNamedSchemaConverter::class),
        ];
    }
}
