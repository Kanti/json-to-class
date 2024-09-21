<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

enum RemoveOldClasses
{
    case NONE;
    case COMPLETE_NAMESPACE;
}
