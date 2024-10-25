<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter;

use Kanti\JsonToClass\Converter\Converter;
use Kanti\JsonToClass\Helpers\F;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

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

    #[Test]
    #[TestDox('Invalid JSON given: "stdClass" allowed: array')]
    public function exception1(): void
    {
        $converter = Converter::getInstance();
        $data = ['a' => 1];
        $this->expectExceptionMessage('Invalid JSON given: "stdClass" allowed: array');
        $converter->jsonDecodeList(F::classString('A'), json_encode($data, flags: JSON_THROW_ON_ERROR));
    }

    #[Test]
    #[TestDox('Invalid JSON given: "array" allowed: object')]
    public function exception2(): void
    {
        $converter = Converter::getInstance();
        $data = [
            ['a' => 1],
        ];
        $this->expectExceptionMessage('Invalid JSON given: "array" allowed: object');
        $converter->jsonDecode(F::classString('A'), json_encode($data, flags: JSON_THROW_ON_ERROR));
    }

    #[Test]
    #[TestDox('Class name must contain namespace')]
    public function exception3(): void
    {
        $converter = Converter::getInstance();
        $this->expectExceptionMessage('Class name must contain namespace');
        $converter->convert(F::classString('A'), []);
    }

    #[Test]
    #[TestDox('If you want to convert an array of objects, use convertList method instead')]
    public function exception4(): void
    {
        $converter = Converter::getInstance();
        $this->expectExceptionMessage('If you want to convert an array of objects, use convertList method instead');
        $converter->convert(F::classString('A\B'), []);
    }

    #[Test]
    #[TestDox('Class name must contain namespace')]
    public function exception5(): void
    {
        $converter = Converter::getInstance();
        $this->expectExceptionMessage('Class name must contain namespace');
        $converter->convertList(F::classString('A'), []);
    }

    #[Test]
    #[TestDox('If you want to convert an object, use convert method instead')]
    public function exception6(): void
    {
        $converter = Converter::getInstance();
        $this->expectExceptionMessage('If you want to convert an object, use convert method instead');
        $converter->convertList(F::classString('A\B'), ['a' => 1]);
    }
}
