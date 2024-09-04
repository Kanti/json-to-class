<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Nette\PhpGenerator\Helpers;

final class PropertyType
{
    public function __construct(
        public readonly string $type,
        public readonly bool $isClass = false,
        public readonly int $listDepth = 0,
        public readonly bool $canBeMissing = false,
        public readonly bool $nullable = false,
    ) {
        if (!$this->isClass) {
            foreach (explode('|', $this->type) as $singleType) {
                if (!in_array($singleType, ['string', 'int', 'float', 'bool', 'null'])) {
                    throw new InvalidArgumentException(sprintf('Unknown type %s', $singleType));
                }
            }
        }
    }

    public function withOneMoreDepth(): PropertyType
    {
        return new self($this->type, $this->isClass, $this->listDepth + 1);
    }

    public function withCanBeMissing(bool $canBeMissing): PropertyType
    {
        return new self($this->type, $this->isClass, $this->listDepth, $canBeMissing, $this->nullable);
    }

    public function getPhpType(): string
    {
        if ($this->listDepth) {
            return 'array';
        }
        return $this->type;
    }

    public function getDocBlockType(string $simplifiedName = ''): string
    {
        return str_repeat('list<', $this->listDepth) . ($simplifiedName ?: $this->type) . str_repeat('>', $this->listDepth);
    }
}
