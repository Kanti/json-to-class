<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use InvalidArgumentException;

final class SchemaMerger
{
    public function merge(?NamedSchema $schema, ?NamedSchema $schemaFromClass): ?NamedSchema
    {
        if (!$schema) {
            return $schemaFromClass;
        }

        if (!$schemaFromClass) {
            return $schema;
        }

        if ($schema->className !== $schemaFromClass->className) {
            throw new InvalidArgumentException('Class names must be the same ' . $schema->className . ' !== ' . $schemaFromClass->className);
        }

        $result = new NamedSchema($schema->className);
        $result->canBeMissing = $schema->canBeMissing || $schemaFromClass->canBeMissing;
        $result->basicTypes = $schema->basicTypes + $schemaFromClass->basicTypes;
        $result->listElement = $this->merge($schema->listElement, $schemaFromClass->listElement);

        if ($schema->properties !== null || $schemaFromClass->properties !== null) {
            $result->properties = [];
        }

        $keys = [...array_keys($schema->properties ?? []), ...array_keys($schemaFromClass->properties ?? [])];
        foreach ($keys as $name) {
            $result->properties[$name] = $this->merge(
                $schema->properties[$name] ?? null,
                $schemaFromClass->properties[$name] ?? null,
            );
        }

        return $result;
    }
}
