<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter;

use Kanti\JsonToClass\Converter\Converter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    #[Test]
    #[TestDox('getInstance() should return the same instance (Singleton)')]
    public function getInstance(): void
    {
        $converter = Converter::getInstance();
        $this->assertInstanceOf(Converter::class, $converter);
        $converter2 = Converter::getInstance();
        $this->assertSame($converter, $converter2);
    }
}
