<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Config;

enum RemoveOldClasses
{
    case NONE;
    case COMPLETE_NAMESPACE;
}
