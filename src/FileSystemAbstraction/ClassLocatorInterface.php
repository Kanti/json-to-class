<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\FileSystemAbstraction;

use Nette\PhpGenerator\ClassType;

interface ClassLocatorInterface
{
    public function getClass(string $className): ClassType;

    public function getFileLocation(string $className): string;
}
