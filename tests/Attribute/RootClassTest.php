<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Attribute;

use Kanti\JsonToClass\Attribute\RootClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RootClassTest extends TestCase
{
    #[Test]
    public function construct(): void
    {
        $rootClass = new RootClass('name');
        $this->assertSame('name', $rootClass->className);
    }
}
