<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use Generator;
use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\SH;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaFromClassCreatorTest extends TestCase
{
    #[Test]
    public function classNotFound(): void
    {
        $container = new JsonToClassContainer();
        $schemaFromClassCreator = $container->get(SchemaFromClassCreator::class);
        $this->assertNull($schemaFromClassCreator->fromClasses(SH::classString(self::class . '\NotExistingClass')));
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function exceptions2(string $classCode, string $expectedExceptionMessage): void
    {
        $container = new JsonToClassContainer();
        $schemaFromClassCreator = $container->get(SchemaFromClassCreator::class);

        $schema = new NamedSchema(Data::class, properties: ['a' => new NamedSchema(SH::classString('Kanti\GeneratedTest\Data\A'))]);
        $class = ClassType::fromCode($classCode);
        $this->assertInstanceOf(ClassType::class, $class);

        $this->expectExceptionMessage($expectedExceptionMessage);
        $schemaFromClassCreator->loopSchema($schema, $class);
    }

    public static function dataProvider(): Generator
    {
        yield 'intersection type' => [
            <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class Data {
    public A&B $a;
}
PHP
,
            'Error in Kanti\GeneratedTest\Data->a: Intersection types not supported',
        ];
        yield 'className mismatch' => [
            <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class DataNot {
    public B $a;
}
PHP
,
            'Class name mismatch Kanti\GeneratedTest\Data\A !== Kanti\GeneratedTest\B this must be a BUG please report it',
        ];
    }
}
