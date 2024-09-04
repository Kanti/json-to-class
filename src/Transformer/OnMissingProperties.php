<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Transformer;

enum OnMissingProperties {
    case THROW_EXCEPTION;
    case SET_DEFAULT;
}
