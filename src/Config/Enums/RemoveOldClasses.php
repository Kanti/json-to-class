<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Enums;

enum RemoveOldClasses
{
    case NONE;
    case COMPLETE_NAMESPACE;
}
