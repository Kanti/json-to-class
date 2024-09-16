<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Validator;

use Exception;
use InvalidArgumentException;
use Kanti\JsonToClass\v2\Config\Config;
use Kanti\JsonToClass\v2\Config\Dto\OnInvalidCharacterProperties;
use Nette\PhpGenerator\Helpers;
use stdClass;

final class Validator
{
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
        assert($data instanceof stdClass || is_array($data));
        foreach ($data as $key => $value) {
            $this->validateKey((string)$key, $config);
            $this->validateData($value, $config);
        }
    }

    private function validateKey(string $key, Config $config): void
    {
        if (Helpers::isIdentifier($key)) {
            return;
        }
        if ($config->onInvalidCharacterProperties === OnInvalidCharacterProperties::REPLACE_INVALID_CHARACTERS_WITH_UNDERSCORE) {
            throw new Exception('Not implemented yet');
        }
        if ($config->onInvalidCharacterProperties === OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE) {
            if (Helpers::isIdentifier('_' . $key)) {
                return;
            }
            throw new InvalidArgumentException('Key is not valid: ' . $key);
        }
        if ($config->onInvalidCharacterProperties === OnInvalidCharacterProperties::THROW_EXCEPTION) {
            throw new InvalidArgumentException('Key is not valid: ' . $key);
        }
    }
}
