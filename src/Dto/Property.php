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
        public string $dataKey,
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
        // TODO could have default value if the original class has a default value
        return false;
    }

    public function getDataKey(): string
    {
        return $this->dataKey;
    }
}
