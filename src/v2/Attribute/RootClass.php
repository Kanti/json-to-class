<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RootClass
{
    public function __construct(
        public string $className,
    ) {
    }
}
