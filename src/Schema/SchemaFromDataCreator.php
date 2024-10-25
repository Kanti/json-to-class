<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Mapper\NameMapper;
use Nette\PhpGenerator\Helpers;
use stdClass;

use function gettype;

final class SchemaFromDataCreator
{
    /**
     * @param array<mixed>|stdClass $data
     */
    public function fromData(array|stdClass $data, Config $config): Schema
    {
        $schema = new Schema();

        $this->generateInternal($data, $schema, $config);

        return $schema;
    }

    /**
     * @param null|bool|int|float|string|array<array-key, mixed>|stdClass $data
     */
    private function generateInternal(null|bool|int|float|string|array|stdClass $data, Schema $currentSchema, Config $config): void
    {
        if (!is_array($data) && !is_object($data)) {
            $currentSchema->basicTypes[$this->getType($data)] = true;
            return;
        }

        $beforeThisRun = $currentSchema->dataKeys;

        $isList = is_array($data) && array_is_list($data);
        if ($isList) {
            $currentSchema->listElement ??= new Schema();
        } else {
            $currentSchema->dataKeys ??= [];
        }

        foreach ((array)$data as $dataKey => $value) {
            if ($isList) {
                $this->generateInternal($value, $currentSchema->listElement, $config);
                continue;
            }

            $currentSchema->dataKeys[$dataKey] ??= new Schema();
            $this->generateInternal($value, $currentSchema->dataKeys[$dataKey], $config);
        }

        if ($isList) {
            return;
        }

        if ($beforeThisRun === null) {
            // skip if it was empty
            return;
        }

        // canBeMissing implementation:
        foreach (array_keys((array)$data) as $dataKey) {
            if (!array_key_exists($dataKey, $beforeThisRun)) {
                // was missing in a previous iteration so it is sometimes unset:
                $currentSchema->dataKeys[$dataKey] ??= new Schema();
                $currentSchema->dataKeys[$dataKey]->canBeMissing = true;
                $currentSchema->dataKeys[$dataKey]->basicTypes['null'] = true;
            }
        }

        foreach (array_keys($beforeThisRun) as $dataKey) {
            if (!array_key_exists($dataKey, (array)$data)) {
                // was missing in current iteration so it is sometimes unset
                assert(isset($currentSchema->dataKeys[$dataKey]));
                $currentSchema->dataKeys[$dataKey]->canBeMissing = true;
                $currentSchema->dataKeys[$dataKey]->basicTypes['null'] = true;
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
