<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use Stringable;
use Nette\PhpGenerator\Helpers;

final readonly class FullyQualifiedClassName implements Stringable
{
    /**
     * @var string without \ at the beginning and end
     */
    public string $namespace;

    public string $className;

    public function __construct(string $fullQualifiedClassName)
    {
        $this->namespace = Helpers::extractNamespace($fullQualifiedClassName);
        $this->className = Helpers::extractShortName($fullQualifiedClassName);
    }

    public function __toString(): string
    {
        return $this->namespace . '\\' . $this->className;
    }
}
