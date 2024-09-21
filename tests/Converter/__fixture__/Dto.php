<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

use Kanti\JsonToClass\Attribute\Types;

final class Dto
{
    /**
     * @param list<Children> $children
     * @param list<list<Children>> $childrenDeep
     * @param list<list<Children>>|list<Children> $childrenMixedDeep
     */
    public function __construct(
        public string $name,
        public int $id,
        public int|float $age,
        #[Types([Children::class])]
        public array $children,
        #[Types([[Children::class]])]
        public array $childrenDeep = [],
        #[Types([[Children::class]], [Children::class])]
        public array $childrenMixedDeep = [],
        public bool|null $isAdult = null,
    ) {
    }
}
