<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Dto;

use Kanti\JsonToClass\Tests\Converter\__fixture__\Dto;
use Kanti\JsonToClass\Tests\Converter\__fixture__\DtoAllowsDynamicProperties;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AbstractJsonClassTraitTest extends TestCase
{
    #[Test]
    public function definedProperties(): void
    {
        $dto = new Dto();
        unset($dto->isAdult);
        unset($dto->name);
        $this->assertNull($dto->isAdult);

        $this->expectExceptionMessage(sprintf('Typed property %s::$%s must not be accessed before initialization', Dto::class, 'name'));
        $dto->__get('name');
    }

    #[Test]
    public function undefinedProperties(): void
    {
        $dto = new DtoAllowsDynamicProperties();
        unset($dto->definedProperty);
        $this->assertNull($dto->definedProperty);
        $this->assertNull($dto->isAdult);

        $dto = new Dto();
        $this->expectExceptionMessage(sprintf('Undefined property %s::$%s', Dto::class, 'helpsMe'));
        $dto->__get('helpsMe');
    }
}
