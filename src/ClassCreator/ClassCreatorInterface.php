<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\ClassCreator;

use Kanti\JsonToClass\Config\Config;
use stdClass;

interface ClassCreatorInterface
{
    public function createClasses(string $className, array|stdClass $data, Config $config): void;
}
