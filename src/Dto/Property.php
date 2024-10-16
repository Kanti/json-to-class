<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

final readonly class Property
{
    /**
     * @param list<Type> $types
     */
    public function __construct(
        public string $name,
        public array $types,
        public bool $isOptional,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasDefaultValue(): bool
    {
        return $this->isOptional;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function getDefaultValue(): mixed
    {
        return null;
    }
}
