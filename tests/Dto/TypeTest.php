<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Dto;

use Kanti\JsonToClass\Dto\Type;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    #[Test]
    public function from(): void
    {
        $type = Type::from('string');
        $this->assertEquals('string', $type->name);
        $this->assertEquals(0, $type->depth);

        $type = Type::from(['string']);
        $this->assertEquals('string', $type->name);
        $this->assertEquals(1, $type->depth);

        $type = Type::from([['string']]);
        $this->assertEquals('string', $type->name);
        $this->assertEquals(2, $type->depth);

        $type = Type::from(Type::class);
        $this->assertEquals(Type::class, $type->name);
        $this->assertEquals(0, $type->depth);

        $type = Type::from([]);
        $this->assertEquals('', $type->name);
        $this->assertEquals(1, $type->depth);
    }
}
