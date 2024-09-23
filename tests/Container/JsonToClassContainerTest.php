<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Container;

use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Schema\SchemaMerger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
}
