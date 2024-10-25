<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Helpers;

use Kanti\JsonToClass\Helpers\F;
use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class FTest extends TestCase
{
    #[Test]
    #[TestDox('Multiple attributes found A')]
    public function getAttribute(): void
    {
        $class = new ClassType(F::classString('A'));
        $class->addAttribute('A', [1]);
        $class->addAttribute('A', [2]);
        $class->addAttribute('A', [3]);
        $this->expectExceptionMessage('Multiple attributes found A');
        F::getAttribute(F::classString('A'), $class);
    }
}
