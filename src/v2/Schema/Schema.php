<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

final class Schema
{
    public function __construct(
        public bool $canBeMissing = false,
        /** @var array<string, true> */
        public array $basicTypes = [],
        public ?Schema $listElement = null,
        /** @var array<string, Schema>|null */
        public ?array $properties = null
    ) {
    }
}
