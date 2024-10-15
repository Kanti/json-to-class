<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

use Kanti\JsonToClass\Attribute\Types;

final class Dto
{
    public string $name;

    public int $id;

    public int|float $age;

    /** @var list<Children> */
    #[Types([Children::class])]
    public array $children;

    /** @var list<list<Children>> */
    #[Types([[Children::class]])]
    public array $childrenDeep = [];

    /** @var list<list<Children>>|list<Children> */
    #[Types([[Children::class]], [Children::class])]
    public array $childrenMixedDeep = [];

    public bool|null $isAdult = null;

    /**
     * @param list<Children> $children
     * @param list<list<Children>> $childrenDeep
     * @param list<list<Children>>|list<Children> $childrenMixedDeep
     */
    public static function from(string $name, int $id, int|float $age, array $children, array $childrenDeep = [], array $childrenMixedDeep = [], bool|null $isAdult = null): self
    {
        $that = new self();
        $that->name = $name;
        $that->id = $id;
        $that->age = $age;
        $that->children = $children;
        $that->childrenDeep = $childrenDeep;
        $that->childrenMixedDeep = $childrenMixedDeep;
        $that->isAdult = $isAdult;
        return $that;
    }
}
