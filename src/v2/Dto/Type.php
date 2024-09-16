<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Dto;

final readonly class Type
{
    /**
     * name can be empty string if the type is an empty array{}
     * @param string $name
     * @param int $depth
     */
    public function __construct(
        public string $name,
        public int $depth = 0,
    ) {
    }

    public static function from(string|array $type): self
    {
        $depth = 0;
        while (is_array($type)) {
            if (count($type) > 1) {
                throw new \InvalidArgumentException('Only one type is allowed');
            }
            $type = $type[0] ?? '';
            $depth++;
        }

        return new self($type, $depth);
    }

    public function unpackOnce(): self
    {
        if ($this->depth === 0) {
            throw new \InvalidArgumentException('Cannot unpack a type with depth 0');
        }

        return new self($this->name, $this->depth - 1);
    }

    public function isClass(): bool
    {
        return $this->depth === 0 && str_contains($this->name, '\\');
    }

    public function isBasicType(): bool
    {
        return $this->depth === 0 && !str_contains($this->name, '\\');
    }

    public function isArray(): bool
    {
        return $this->depth > 0;
    }
}
