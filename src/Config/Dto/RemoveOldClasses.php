<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config\Dto;

enum RemoveOldClasses
{
    case NONE;
    case COMPLETE_NAMESPACE;
}
