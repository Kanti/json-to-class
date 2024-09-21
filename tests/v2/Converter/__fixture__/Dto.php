<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\Converter\__fixture__;

use Kanti\JsonToClass\v2\Attribute\Types;

final class Dto
{
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
