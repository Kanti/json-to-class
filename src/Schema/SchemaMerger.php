<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;

final class SchemaMerger
{
    /**
     * @return ($schemaA is NamedSchema ? NamedSchema : ($schemaB is NamedSchema ? NamedSchema : null))
     */
    public function merge(?NamedSchema $schemaA, ?NamedSchema $schemaB, bool $isProperty = true): ?NamedSchema
    {
        if (!$schemaA) {
            if ($schemaB && $isProperty) {
                $schemaB->canBeMissing = true;
            }

            return $schemaB;
        }

        if (!$schemaB) {
            if ($isProperty) {
                $schemaA->canBeMissing = true;
            }

            return $schemaA;
        }

        if ($schemaA->className !== $schemaB->className) {
            throw new InvalidArgumentException('Class names must be the same ' . $schemaA->className . ' !== ' . $schemaB->className);
        }

        $result = new NamedSchema($schemaA->className);
        $result->canBeMissing = $schemaA->canBeMissing || $schemaB->canBeMissing;
        $result->basicTypes = $schemaA->basicTypes + $schemaB->basicTypes;
        $result->listElement = $this->merge($schemaA->listElement, $schemaB->listElement, false);

        if ($schemaA->properties === null && $schemaB->properties === null) {
            return $result;
        }

        if ($schemaA->properties === null xor $schemaB->properties === null) {
            $result->properties = $schemaA->properties ?? $schemaB->properties;
            return $result;
        }

        $result->properties = [];

        $keys = [...array_keys($schemaA->properties ?? []), ...array_keys($schemaB->properties ?? [])];
        foreach ($keys as $name) {
            $propertySchema = $this->merge($schemaA->properties[$name] ?? null, $schemaB->properties[$name] ?? null, true);
            assert($propertySchema instanceof NamedSchema);
            $result->properties[$name] = $propertySchema;
        }

        return $result;
    }
}
