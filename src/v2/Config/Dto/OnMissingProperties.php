<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Config\Dto;

enum OnMissingProperties
{
    case GUESS;
    case THROW_EXCEPTION;
}
