<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Schema;

use Generator;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\SchemaFromClassCreator;
use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaFromClassCreatorTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function exceptions(string $classCode, string $expectedExceptionMessage): void
    {
        $container = new JsonToClassContainer([
        ]);
        $schemaFromClassCreator = $container->get(SchemaFromClassCreator::class);
        assert($schemaFromClassCreator instanceof SchemaFromClassCreator);
        $schema = new NamedSchema('Kanti\GeneratedTest\Data', properties: ['a' => new NamedSchema('Kanti\GeneratedTest\Data\A')]);
        $class = ClassType::fromCode($classCode);
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
  public function __construct(
    public A&B $a,
  ) {}
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
  public function __construct(
    public B $a,
  ) {}
}
PHP
,
            'Class name mismatch Kanti\GeneratedTest\Data\A !== Kanti\GeneratedTest\B this must be a BUG please report it',
        ];
        yield 'parameter is not a promoted property' => [
            <<<'PHP'
<?php
namespace Kanti\GeneratedTest;
class Data {
  public function __construct(
    B $b,
  ) {}
}
PHP
            ,
            'Parameter is not a PromotedParameter Kanti\GeneratedTest\Data->b',
            ];
    }
}
