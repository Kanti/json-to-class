<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
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

    public static function toProperty(string $key, Config $config): string
    {
        if (Helpers::isIdentifier($key)) {
            return $key;
        }

        return match ($config->onInvalidCharacterProperties) {
            OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE => Helpers::isIdentifier('_' . $key) ? '_' . $key : throw new InvalidArgumentException('key ' . $key . ' is not a valid property name (even with _ prefix)'),
            OnInvalidCharacterProperties::THROW_EXCEPTION => throw new InvalidArgumentException('key ' . $key . ' is not a valid property name'),
        };
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

        $beforeThisRun = $currentSchema->properties;

        $isList = is_array($data) && array_is_list($data);
        if ($isList) {
            $currentSchema->listElement ??= new Schema();
        } else {
            $currentSchema->properties ??= [];
        }

        foreach ((array)$data as $property => $value) {
            if ($isList) {
                $this->generateInternal($value, $currentSchema->listElement, $config);
                continue;
            }

            $property = SchemaFromDataCreator::toProperty($property, $config);
            $currentSchema->properties[$property] ??= new Schema();
            $this->generateInternal($value, $currentSchema->properties[$property], $config);
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
                $property = SchemaFromDataCreator::toProperty($property, $config);
                // was missing in a previous iteration so it is sometimes unset:
                $currentSchema->properties[$property] ??= new Schema();
                $currentSchema->properties[$property]->canBeMissing = true;
                $currentSchema->properties[$property]->basicTypes['null'] = true;
            }
        }

        foreach (array_keys($beforeThisRun) as $property) {
            if (!array_key_exists($property, (array)$data)) {
                $property = SchemaFromDataCreator::toProperty($property, $config);
                // was missing in current iteration so it is sometimes unset
                assert(isset($currentSchema->properties[$property]));
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
