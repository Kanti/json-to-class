<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

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
    public function readableClass(): void
    {
        $classCode = <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class Data {
    public ?string $a = null;
}
PHP;
        $schemaFromClassCreator = $this->getSchemaFromClassCreator();

        $schema = new NamedSchema(Data::class);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    private function getSchemaFromClassCreator(): SchemaFromClassCreator
    {
        return (new JsonToClassContainer())->get(SchemaFromClassCreator::class);
    }
}
