<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Dto;

use Kanti\JsonToClass\Dto\Type;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    #[Test]
    #[TestDox('test for exceptions')]
    public function from(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only one type is allowed');
        Type::from(['string', 'int']);
    }
}
