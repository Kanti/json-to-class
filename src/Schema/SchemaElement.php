<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;

final class SchemaElement
{
    public function __construct(
        /** @var array<string, true> */
        public array $basicTypes = [],
        public ?SchemaElement $listElement = null,
        /** @var array<string, SchemaElement> */
        public array $properties = [],
        public bool $canBeMissing = false,
    ) {
    }

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
        usort($basicTypes, static function (string|int $a, string|int $b): int {
            $ranking = [
                'string' => 1,
                'float' => 2,
                'int' => 3,
                'bool' => 4,
                'null' => 5,
            ];

            $aInt = $ranking[$a] ?? throw new InvalidArgumentException(sprintf('Unknown type %s', $a));
            $bInt = $ranking[$b] ?? throw new InvalidArgumentException(sprintf('Unknown type %s', $b));
            return $aInt <=> $bInt;
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

    public function getTypeName(FullyQualifiedClassName $fullyQualifiedNamespace): PropertyType
    {
        if ($this->isEmpty()) {
            return new PropertyType('array');
        }

        if ($this->basicTypes) {
            return new PropertyType($this->getBasicTypesString());
        }

        if ($this->listElement) {
            return $this->listElement->getTypeName($fullyQualifiedNamespace)->withOneMoreDepth();
        }

        return new PropertyType($fullyQualifiedNamespace->__toString(), isClass: true);
    }

    public function isEmpty(): bool
    {
        if ($this->basicTypes) {
            return false;
        }

        if ($this->properties) {
            return false;
        }

        return !$this->listElement;
    }
}
