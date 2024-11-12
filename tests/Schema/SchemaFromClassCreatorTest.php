<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Composer\Autoload\ClassLoader;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class SchemaFromClassCreatorTest extends TestCase
{
    #[Test]
    public function classNotFound(): void
    {
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();
        $this->assertNull($schemaFromClassCreator->fromClasses(F::classString(self::class . '\NotExistingClass')));
    }

    #[Test]
    #[TestDox('Error in Kanti\GeneratedTest\Data->a: Intersection types not supported')]
    public function exception1(): void
    {
        $classCode = <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class Data {
    public A&B $a;
}
PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class, properties: ['a' => new NamedSchema(F::classString('Kanti\GeneratedTest\Data\A'))]);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $this->expectExceptionMessage('Error in Kanti\GeneratedTest\Data->a: Intersection types not supported');
        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    #[Test]
    #[TestDox('Error in Kanti\GeneratedTest\Data->a: Union type must have a single type')]
    public function exception2(): void
    {
        $classCode = <<<'PHP'
            <?php
            namespace Kanti\GeneratedTest;
            class Data {
                public (A&B)|C $a;
            }
            PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class, properties: ['a' => new NamedSchema(F::classString('Kanti\GeneratedTest\Data\A'))]);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $this->expectExceptionMessage('Error in Kanti\GeneratedTest\Data->a: Union type must have a single type');
        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    #[Test]
    #[TestDox('Error in Kanti\GeneratedTest\Data->a: Type is not defined')]
    public function exception3(): void
    {
        $classCode = <<<'PHP'
            <?php
            namespace Kanti\GeneratedTest;
            class Data {
                public $a;
            }
            PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class, properties: ['a' => new NamedSchema(F::classString('Kanti\GeneratedTest\Data\A'))]);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $this->expectExceptionMessage('Error in Kanti\GeneratedTest\Data->a: Type is not defined');
        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    #[Test]
    #[TestDox('Class not found Kanti\GeneratedTest\A')]
    public function exception4(): void
    {
        $classCode = <<<'PHP'
            <?php
            namespace Kanti\GeneratedTest;
            class Data {
                public A $a;
            }
            PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class, properties: ['a' => new NamedSchema(F::classString('Kanti\GeneratedTest\Data\A'))]);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $this->expectExceptionMessage('Class not found Kanti\GeneratedTest\A');
        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    #[Test]
    public function readableClass(): void
    {
        $classCode = <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class Data {
    public string|float|int|null $a = null;
}
PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $schemaFromClassCreator->loopSchema($schema, $class);
        $this->assertIsArray($schema->properties);
        $this->assertArrayHasKey('a', $schema->properties);
        $this->assertTrue($schema->properties['a']->canBeMissing);
        $this->assertEquals([
            'string' => true,
            'float' => true,
            'int' => true,
            'null' => true,
        ], $schema->properties['a']->basicTypes);
    }

    #[Test]
    public function doNotOverwriteListElementWithEmptyArray(): void
    {
        $classCode = <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
use Kanti\JsonToClass\Attribute\Types;

class Data {
    #[Types(['string'], [])]
    public array $a = null;
}
PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $schemaFromClassCreator->loopSchema($schema, $class);
        $expected = new NamedSchema(F::classString('Kanti\GeneratedTest\Data\A_'), basicTypes: ['string' => true]);
        $this->assertIsArray($schema->properties);
        $this->assertArrayHasKey('a', $schema->properties);
        $this->assertEquals($expected, $schema->properties['a']->listElement);
    }

    private function getSchemaFromClassCreator(): SchemaFromClassCreator
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('Kanti\\', 'a');
        return (new JsonToClassContainer([
            ClassLoader::class => $classLoader,
        ]))->get(SchemaFromClassCreator::class);
    }
}
