<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;

final class SchemaMerger
{
    /**
     * @return ($schemaA is NamedSchema ? NamedSchema : ($schemaB is NamedSchema ? NamedSchema : null))
     */
    public function merge(?NamedSchema $schemaA, ?NamedSchema $schemaB): ?NamedSchema
    {
        if (!$schemaA) {
            return $schemaB;
        }

        if (!$schemaB) {
            return $schemaA;
        }

        if ($schemaA->className !== $schemaB->className) {
            throw new InvalidArgumentException('Class names must be the same ' . $schemaA->className . ' !== ' . $schemaB->className);
        }

        $result = new NamedSchema($schemaA->className);
        $result->canBeMissing = $schemaA->canBeMissing || $schemaB->canBeMissing;
        $result->basicTypes = $schemaA->basicTypes + $schemaB->basicTypes;
        $result->listElement = $this->merge($schemaA->listElement, $schemaB->listElement);

        if (is_array($schemaA->properties)) {
            $result->properties = [];
        }

        if (is_array($schemaB->properties)) {
            $result->properties = [];
        }

        $keys = [...array_keys($schemaA->properties ?? []), ...array_keys($schemaB->properties ?? [])];
        foreach ($keys as $name) {
            $propertySchema = $this->merge($schemaA->properties[$name] ?? null, $schemaB->properties[$name] ?? null);
            assert($propertySchema instanceof NamedSchema);
            $result->properties[$name] = $propertySchema;
        }

        return $result;
    }
}
