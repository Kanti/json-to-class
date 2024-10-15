<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

final class SchemaSimplification
{
    public function simplify(NamedSchema $schema): ?NamedSchema
    {
        if ($this->canBeSimplified($schema)) {
            // remove schema element that is only null able
            return null;
        }

        if ($schema->listElement) {
            $schema->listElement = $this->simplify($schema->listElement);
        }

        if ($this->canBeSimplified($schema)) {
            return null;
        }

        if ($schema->properties === null) {
            return $schema;
        }

        $schema->properties = array_filter($schema->properties, fn($property): bool => $this->simplify($property) !== null);

        if ($this->canBeSimplified($schema)) {
            return null;
        }

        return $schema;
    }

    private function canBeSimplified(NamedSchema $schema): bool
    {
        if ($schema->properties) {
            return false;
        }

        if ($schema->listElement) {
            return false;
        }

        if ($schema->basicTypes) {
            // remove schema element that is only nullable
            return $schema->basicTypes === ['null' => true];
        }

        // remove schema element that has no type at all
        return true;
    }
}
