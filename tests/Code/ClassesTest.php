<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Code;

use Exception;
use Kanti\JsonToClass\Code\Classes;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Nette\PhpGenerator\PhpFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClassesTest extends TestCase
{
    #[Test]
    public function addClass(): void
    {
        $classes = new Classes();

        $className = new FullyQualifiedClassName(FullyQualifiedClassName::class);
        $classes->addClass($className, 'Content');
        $this->assertCount(1, $classes);

        $classes2 = new Classes();
        $classes2->addClass($className, 'Content');
        $this->assertCount(1, $classes);

        $this->expectException(Exception::class);
        $classes->add($classes2);
    }
}
