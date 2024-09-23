<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Validator;

use Exception;
use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Dto\OnInvalidCharacterProperties;
use Nette\PhpGenerator\Helpers;
use stdClass;

final class Validator
{
    /**
     * @param bool|int|float|string|array<mixed>|stdClass|null $data
     */
    public function validateData(null|bool|int|float|string|array|stdClass $data, Config $config): void
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
            foreach ($data as $item) {
                $this->validateData($item, $config);
            }

            return;
        }

        foreach ((array)$data as $key => $value) {
            $this->validateKey((string)$key, $config);
            $this->validateData($value, $config);
        }
    }

    private function validateKey(string $key, Config $config): void
    {
        $isValid = match ($config->onInvalidCharacterProperties) {
            OnInvalidCharacterProperties::THROW_EXCEPTION => Helpers::isIdentifier($key),
            OnInvalidCharacterProperties::REPLACE_INVALID_CHARACTERS_WITH_UNDERSCORE => throw new Exception('Not implemented yet'),
            OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE => Helpers::isIdentifier($key) || Helpers::isIdentifier('_' . $key),
        };
        if (!$isValid) {
            throw new InvalidArgumentException('Key is not valid: ' . $key);
        }
    }
}
