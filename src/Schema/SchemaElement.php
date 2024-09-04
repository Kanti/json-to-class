<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use Kanti\JsonToClass\Dto\FullyQualifiedClassName;

final class SchemaElement
{
    public function __construct(
        /** @var list<string> */
        public array $basicTypes = [],
        public ?SchemaElement $listElement = null,
        /** @var array<string, SchemaElement> */
        public array $properties = [],
        public bool $canBeMissing = false,
    ) {}

    /**
     * @return list<string>
     */
    public function getBasicTypes(): array
    {
        $basicTypes1 = $this->basicTypes;
        if ($this->canBeMissing) {
            $basicTypes1['null'] = true;
        }
        $basicTypes = array_keys($basicTypes1);
        usort($basicTypes, static function (string $a, string $b) {
            return match ($a) {
                    'string' => 1,
                    'float' => 2,
                    'int' => 3,
                    'bool' => 4,
                    'null' => 5,
                } <=> match ($b) {
                    'string' => 1,
                    'float' => 2,
                    'int' => 3,
                    'bool' => 4,
                    'null' => 5,
                };
        });
        return $basicTypes;
    }

    public function getBasicTypesString(): string
    {
        return implode('|', $this->getBasicTypes());
    }

    /**
     * @return array<string, SchemaElement>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function isValid(): bool
    {
        $count = (int)(bool)$this->basicTypes + (int)(bool)$this->properties + (int)(bool)$this->listElement;
        if ($count > 1) {
            return false;
        }
        if ($this->basicTypes) {
            return true;
        }
        if ($this->listElement) {
            return true;
        }
        foreach ($this->properties as $property) {
            if (!$property->isValid()) {
                return false;
            }
        }
        return true;
    }

    public function getTypeName(FullyQualifiedClassName $fullyQualifiedNamespace): PropertyType
    {
        if ($this->basicTypes) {
            return new PropertyType($this->getBasicTypesString(), nullable: $this->canBeMissing, canBeMissing: $this->canBeMissing);
        }
        if ($this->listElement) {
            return $this->listElement->getTypeName($fullyQualifiedNamespace)->withOneMoreDepth()->withCanBeMissing($this->canBeMissing);
        }
        return new PropertyType($fullyQualifiedNamespace->__toString(), isClass: true, nullable: $this->canBeMissing, canBeMissing: $this->canBeMissing);
    }
}
