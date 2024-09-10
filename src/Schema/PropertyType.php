<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;

final readonly class PropertyType
{
    public function __construct(
        public string $type,
        public bool $isClass = false,
        public int $listDepth = 0,
    ) {
        if (!$this->isClass) {
            foreach (explode('|', $this->type) as $singleType) {
                if (!in_array($singleType, ['array', 'string', 'int', 'float', 'bool', 'null'])) {
                    throw new InvalidArgumentException(sprintf('Unknown type %s', $singleType));
                }
            }
        }
    }

    public function withOneMoreDepth(): PropertyType
    {
        return new self($this->type, $this->isClass, $this->listDepth + 1);
    }

    public function getDocBlockType(string $simplifiedName = ''): string
    {
        return str_repeat('list<', $this->listDepth) . ($simplifiedName ?: $this->type) . str_repeat('>', $this->listDepth);
    }
}
