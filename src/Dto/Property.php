<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use function in_array;

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
        // TODO could have default value if the original class has a default value
        return false;
    }

    public function getType(): object
    {
        if (in_array(new Type('null'), $this->types, false)) {
            return new class {
                public function allowsNull(): bool
                {
                    return true;
                }
            };
        }

        return new class {
            public function allowsNull(): bool
            {
                return false;
            }
        };
    }
}
