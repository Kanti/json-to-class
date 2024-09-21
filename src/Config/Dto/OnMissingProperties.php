<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Dto;

enum OnMissingProperties
{
    case GUESS;
    case THROW_EXCEPTION;
}
