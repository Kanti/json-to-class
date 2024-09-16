<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Config\Dto;

enum OnInvalidCharacterProperties
{
    case THROW_EXCEPTION;

    case TRY_PREFIX_WITH_UNDERSCORE;
    case REPLACE_INVALID_CHARACTERS_WITH_UNDERSCORE;
}
