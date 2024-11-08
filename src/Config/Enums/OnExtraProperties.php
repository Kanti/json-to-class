<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Enums;

enum OnExtraProperties
{
    case IGNORE;
    case THROW_EXCEPTION;
    case ADD_TO_EXTRA_PROPERTIES;
}
