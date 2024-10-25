<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Mapper;

use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\Mapper\PossibleConvertTargets;
use Nette\PhpGenerator\PsrPrinter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class PossibleConvertTargetsTest extends TestCase
{
    #[Test]
    public function getNoMatch(): void
    {
        $possibleConvertTargets = new PossibleConvertTargets([]);
        $this->assertNull($possibleConvertTargets->getMatch(new Type('string')));
    }

    #[Test]
    public function stringify(): void
    {
        $possibleConvertTargets = new PossibleConvertTargets([
            new Type('string', 1),
            new Type('string', 0),
        ]);
        $this->assertSame('string[]|string', $possibleConvertTargets->__toString());
    }

    /** @phpstan-ignore-next-line */
    public PsrPrinter&PossibleConvertTargets $intersectionType;

    /** @phpstan-ignore-next-line */
    public (PsrPrinter&PossibleConvertTargets)|string $intersectionInUnionType;

    #[Test]
    #[TestDox('Intersection types are not supported')]
    public function exception1(): void
    {
        $this->expectExceptionMessage('Intersection types are not supported');
        PossibleConvertTargets::fromParameter(new ReflectionProperty($this, 'intersectionType'));
    }

    #[Test]
    #[TestDox('Only named types are supported in union types')]
    public function exception2(): void
    {
        $this->expectExceptionMessage('Only named types are supported in union types');
        PossibleConvertTargets::fromParameter(new ReflectionProperty($this, 'intersectionInUnionType'));
    }
}
