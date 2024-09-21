<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

final class Children
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}
