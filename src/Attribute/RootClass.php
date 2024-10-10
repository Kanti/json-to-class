<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RootClass
{
    /**
     * @param class-string|null $className
     */
    public function __construct(
        public ?string $className = null,
    ) {
    }
}
