<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Logger\Dto;

use Kanti\JsonToClass\Logger\Dto\Writeable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class WriteableTest extends TestCase
{
    #[Test]
    #[TestDox('$resource must be a resource')]
    public function exception1(): void
    {
        $this->expectExceptionMessage('$resource must be a resource');
        new Writeable(123);
    }

    #[Test]
    public function constructor(): void
    {
        $this->assertInstanceOf(Writeable::class, new Writeable('php://stderr'));
    }
}
