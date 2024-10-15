<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Enums;

enum OnInvalidCharacterProperties
{
    case THROW_EXCEPTION;

    case TRY_PREFIX_WITH_UNDERSCORE;
}
