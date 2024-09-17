<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use stdClass;

final class SchemaFromDataCreator implements SchemaFromDataCreatorInterface
{
    public function fromData(array|stdClass $data): Schema
    {
        $schema = new Schema();

        $this->generateInternal($data, $schema);

        return $schema;
    }

    /**
     * @param null|bool|int|float|string|array<array-key, mixed>|stdClass $data
     */
    private function generateInternal(null|bool|int|float|string|array|stdClass $data, Schema $currentSchema): void
    {
        if (!is_array($data) && !is_object($data)) {
            $currentSchema->basicTypes[$this->getType($data)] = true;
            return;
        }

        $beforeThisRun = $currentSchema->properties;

        $isList = is_array($data) && array_is_list($data);
        if ($isList) {
            $currentSchema->listElement ??= new Schema();
        } else {
            $currentSchema->properties ??= [];
        }

        foreach ($data as $property => $value) {
            if ($isList) {
                $this->generateInternal($value, $currentSchema->listElement);
                continue;
            }

            $currentSchema->properties[$property] ??= new Schema();
            $this->generateInternal($value, $currentSchema->properties[$property]);
        }

        if ($isList) {
            return;
        }

        if ($beforeThisRun === null) {
            // skip if it was empty
            return;
        }

        // canBeMissing implementation:
        foreach (array_keys((array)$data) as $property) {
            if (!array_key_exists($property, $beforeThisRun)) {
                // was missing in a previous iteration so it is sometimes unset:
                $currentSchema->properties[$property] ??= new Schema();
                $currentSchema->properties[$property]->canBeMissing = true;
                $currentSchema->properties[$property]->basicTypes['null'] = true;
            }
        }

        foreach (array_keys($beforeThisRun) as $property) {
            if (!array_key_exists($property, (array)$data)) {
                // was missing in current iteration so it is sometimes unset
                $currentSchema->properties[$property]->canBeMissing = true;
                $currentSchema->properties[$property]->basicTypes['null'] = true;
            }
        }
    }

    private function getType(null|bool|int|float|string $data): string
    {
        $type = gettype($data);
        return match ($type) {
            'NULL' => 'null',
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => $type,
        };
    }
}
