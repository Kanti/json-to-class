<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Validator;

use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Nette\PhpGenerator\Helpers;
use stdClass;

use function get_debug_type;
use function is_array;

final class Validator
{
    /**
     * @param bool|int|float|string|array<mixed>|stdClass|null $data
     */
    public function validateData(null|bool|int|float|string|array|stdClass $data, Config $config): void
    {
        $this->validateDataInternal($data, $config, '$');
    }

    private function validateDataInternal(mixed $data, Config $config, string $path): void
    {
        if (is_null($data)) {
            return;
        }

        if (is_bool($data)) {
            return;
        }

        if (is_int($data)) {
            return;
        }

        if (is_float($data)) {
            return;
        }

        if (is_string($data)) {
            return;
        }

        if (is_array($data) && array_is_list($data)) {
            foreach ($data as $index => $item) {
                $this->validateDataInternal($item, $config, $path . '[' . $index . ']');
            }

            return;
        }

        if (!is_array($data) && !$data instanceof stdClass) {
            throw new InvalidArgumentException('Data is not of valid type ' . get_debug_type($data) . ' valid: ' . $path);
        }

        foreach ((array)$data as $key => $value) {
            $this->validateKey((string)$key, $config, $path);
            $this->validateDataInternal($value, $config, $path . '.' . $key);
        }
    }

    private function validateKey(string $key, Config $config, string $path): void
    {
        $isValid = match ($config->onInvalidCharacterProperties) {
            OnInvalidCharacterProperties::THROW_EXCEPTION => Helpers::isIdentifier($key),
            OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE => Helpers::isIdentifier($key) || Helpers::isIdentifier('_' . $key),
        };
        if (!$isValid) {
            throw new InvalidArgumentException('Key is not valid: ' . $key . ' at ' . $path);
        }
    }
}
