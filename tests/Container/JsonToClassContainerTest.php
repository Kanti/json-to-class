<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Container;

use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\SchemaMerger;
use Kanti\JsonToClass\Tests\Converter\__fixture__\DiffrentKeys;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

use function Safe\class_alias;
use function microtime;
use function str_replace;

class JsonToClassContainerTest extends TestCase
{
    #[Test]
    public function get(): void
    {
        $container = new JsonToClassContainer();
        $this->assertInstanceOf(SchemaMerger::class, $container->get(SchemaMerger::class));
    }

    #[Test]
    public function has(): void
    {
        $container = new JsonToClassContainer();
        $this->assertTrue($container->has(SchemaMerger::class));
        $this->assertFalse($container->has(SchemaMerger2000000000::class));
    }

    #[Test]
    #[TestDox('Parameter A...->x has no type')]
    public function exception1(): void
    {
        $container = new JsonToClassContainer();
        $className = F::classString('A' . str_replace('.', '_', (string)microtime(true)));
        class_alias(
            (new class {
                public function __construct($x = null)
                {
                }
            })::class,
            $className,
        );
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Parameter ' . $className . '->x has no type');
        $container->get($className);
    }

    #[Test]
    #[TestDox('Parameter A...->x type not possible ?string')]
    public function exception2(): void
    {
        $container = new JsonToClassContainer();
        $className = F::classString('A' . str_replace('.', '_', (string)microtime(true)));
        class_alias(
            (new class (null) {
                public function __construct(?string $x)
                {
                }
            })::class,
            $className,
        );
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Parameter ' . $className . '->x type not possible ?string');
        $container->get($className);
    }

    #[Test]
    #[TestDox('Factory for A not callable or instance of A given: stdClass')]
    public function exception3(): void
    {
        $class = DiffrentKeys::class;
        $container = new JsonToClassContainer([
            $class => new stdClass(),
        ]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Factory for ' . $class . ' is not callable or instance of ' . $class . ' given: stdClass');
        $container->get($class);
    }

    #[Test]
    #[TestDox('Factory for A not callable or instance of A given: stdClass')]
    public function exception4(): void
    {
        $class = DiffrentKeys::class;
        $container = new JsonToClassContainer([
            $class => fn(): stdClass => new stdClass(),
        ]);

        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Factory for ' . $class . ' dose not produce instance of ' . $class . ' but stdClass');
        $container->get($class);
    }

    #[Test]
    #[TestDox('Autoload file not found 😿')]
    public function exception5(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Autoload file not found 😿');
        JsonToClassContainer::getClassLoader([]);
    }
}
