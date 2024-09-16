<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\ClassCreator;

use Kanti\JsonToClass\v2\Config\Config;
use stdClass;

interface ClassCreatorInterface
{
    public function createClasses(string $className, array|stdClass $data, Config $config): void;
}
