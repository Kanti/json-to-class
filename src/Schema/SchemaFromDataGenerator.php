<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use Exception;

final class SchemaFromDataGenerator
{
    /**
     * @param null|bool|int|float|string|array<array-key, mixed> $data
     */
    public function generate(null|bool|int|float|string|array $data): SchemaElement
    {
        $schema = new SchemaElement();

        $this->generateInternal($data, $schema);

        return $schema;
    }

    /**
     * @param null|bool|int|float|string|array<array-key, mixed> $data
     */
    public function generateInternal(null|bool|int|float|string|array $data, SchemaElement $currentSchema): void
    {
        if (!is_array($data)) {
            $currentSchema->basicTypes[$this->getType($data)] = true;
            return;
        }

        $isList = array_is_list($data);
        $beforeThisRun = $currentSchema->properties;
        foreach ($data as $property => $value) {
            if ($isList) {
                $currentSchema->listElement ??= new SchemaElement();
                $this->generateInternal($value, $currentSchema->listElement);
                continue;
            }

            $currentSchema->properties[$property] ??= new SchemaElement();
            $this->generateInternal($value, $currentSchema->properties[$property]);
        }

        if ($isList) {
            return;
        }

        if ($beforeThisRun) {
            foreach (array_keys($data) as $property) {
                if (!array_key_exists($property, $beforeThisRun)) {
                    // was missing in current iteration so it is sometimes unset
                    $currentSchema->properties[$property] ??= new SchemaElement();
                    $currentSchema->properties[$property]->canBeMissing = true;
                }
            }

            foreach (array_keys($beforeThisRun) as $property) {
                if (!array_key_exists($property, $data)) {
                    // was missing in current iteration so it is sometimes unset
                    $currentSchema->properties[$property] ??= new SchemaElement();
                    $currentSchema->properties[$property]->canBeMissing = true;
                }
            }
        }
    }

    private function getType(float|bool|int|string|null $data): string
    {
        return match (gettype($data)) {
            'NULL' => 'null',
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => gettype($data),
        };
    }
}
