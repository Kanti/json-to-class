<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Dto;

use InvalidArgumentException;
use Kanti\JsonToClass\Dto\Type;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    #[Test]
    #[TestDox('Type name cannot start with a backslash')]
    public function exception1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type name cannot start with a backslash');
        new Type('\Kanti\Test');
    }

    #[Test]
    #[TestDox('Depth must be a 0 or higher')]
    public function exception2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Depth must be a 0 or higher');
        new Type(self::class, -1);
    }

    #[Test]
    #[TestDox('Empty array must have depth 1')]
    public function exception3(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty array must have depth 1');
        new Type('', 0);
    }

    #[Test]
    #[TestDox('test for exceptions ::from')]
    public function from(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only one type is allowed');
        Type::from(['string', 'int']);
    }

    #[Test]
    #[TestDox('Class does not exist ::getClassName')]
    public function getClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class does not exist');
        Type::from('\Kanti\JsonToClass\Tests\Dto\int')->getClassName();
    }

    #[Test]
    #[TestDox('Class does not exist ::getClassName')]
    public function unpackOnce(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot unpack a type with depth 0');
        Type::from('string')->unpackOnce();
    }

    #[Test]
    public function testToString(): void
    {
        $this->assertEquals('bool', Type::from('bool')->__toString());
        $this->assertEquals(self::class, Type::from(self::class)->__toString());
        $this->assertEquals(self::class . '[]', Type::from([self::class])->__toString());
        $this->assertEquals('string[]', Type::from(['string'])->__toString());
        $this->assertEquals('string[][]', Type::from([['string']])->__toString());
    }
}
