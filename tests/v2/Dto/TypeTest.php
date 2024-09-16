<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Dto;

use InvalidArgumentException;
use Kanti\JsonToClass\v2\Dto\Type;
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
