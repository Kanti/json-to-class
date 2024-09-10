<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Schema\PropertyType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PropertyTypeTest extends TestCase
{
    #[Test]
    public function construct(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PropertyType('FakeClassAndNotMarkedAsClass');
    }
}
