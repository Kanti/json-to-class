<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Key
{
    public function __construct(public string $key)
    {
    }
}
