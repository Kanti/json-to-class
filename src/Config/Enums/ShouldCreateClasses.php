<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Enums;

enum ShouldCreateClasses
{
    case TRY_TO_DETECT;
    case NO;
    case YES;
}
