<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Helpers\F;

final class NamedSchema
{
    public function __construct(
        /** @var class-string $className */
        public string $className,
        public ?string $dataKey = null,
        public bool $canBeMissing = false,
        /** @var array<string, true> */
        public array $basicTypes = [],
        public ?NamedSchema $listElement = null,
        /** @var array<string, NamedSchema>|null */
        public ?array $properties = null,
    ) {
    }

    /**
     * @phpstan-assert-if-true NamedSchema $this->listElement
     */
    public function isOnlyAList(): bool
    {
        if ($this->basicTypes) {
            return false;
        }

        if ($this->properties !== null) {
            return false;
        }

        if ($this->canBeMissing) {
            return false;
        }

        return (bool)$this->listElement;
    }

    public function getFirstNonListChild(): NamedSchema
    {
        $schema = $this;
        while ($schema->isOnlyAList()) {
            $schema = $schema->listElement;
        }

        return $schema;
    }
}
