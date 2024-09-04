<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Code;

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
        $classes->addClass(new FullyQualifiedClassName('Kanti\JsonToClass\Dto\FullyQualifiedClassName'), new PhpFile());
        $this->assertCount(1, $classes);
        $classes2 = new Classes();
        $classes2->addClass(new FullyQualifiedClassName('Kanti\JsonToClass\Dto\FullyQualifiedClassName'), new PhpFile());
        $this->assertCount(1, $classes);

        $this->expectException(\Exception::class);
        $classes->add($classes2);
    }
}
