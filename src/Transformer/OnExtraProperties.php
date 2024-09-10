<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Transformer;

enum OnExtraProperties
{
    case THROW_EXCEPTION;
    case IGNORE;
}
