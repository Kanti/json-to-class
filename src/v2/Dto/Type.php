<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Dto;

use InvalidArgumentException;

final readonly class Type
{
    /**
     * name can be empty string if the type is an empty array{}
     */
    public function __construct(
        public string $name,
        public int $depth = 0,
    ) {
        if (str_starts_with($name, '\\')) {
            throw new InvalidArgumentException('Type name cannot start with a backslash');
        }

        if ($depth < 0) {
            throw new InvalidArgumentException('Depth must be a 0 or higher');
        }

        if ($name === '' && $depth === 0) {
            throw new InvalidArgumentException('Empty array must have depth 1');
        }
    }

    public static function from(string|array $type): self
    {
        $depth = 0;
        while (is_array($type)) {
            if (count($type) > 1) {
                throw new InvalidArgumentException('Only one type is allowed');
            }

            $type = $type[0] ?? '';
            $depth++;
        }

        return new self(ltrim((string) $type, '\\'), $depth);
    }

    public function unpackOnce(): self
    {
        if ($this->depth === 0) {
            throw new InvalidArgumentException('Cannot unpack a type with depth 0');
        }

        return new self($this->name, $this->depth - 1);
    }

    public function isClass(): bool
    {
        return !$this->isArray() && !$this->isBasicType();
    }

    public function isBasicType(): bool
    {
        return !$this->isArray() && !str_contains($this->name, '\\') && $this->name !== 'object';
    }

    public function isArray(): bool
    {
        return $this->depth > 0;
    }

    public function isEmptyArray(): bool
    {
        return $this->depth === 1 && $this->name === '';
    }


    public static function fromData(mixed $param): self
    {
        if (is_null($param)) {
            return new self('null');
        }
        if (is_bool($param)) {
            return new self('bool');
        }
        if (is_int($param)) {
            return new self('int');
        }
        if (is_float($param)) {
            return new self('float');
        }
        if (is_string($param)) {
            return new self('string');
        }
        if (is_array($param)) {
            if (array_is_list($param)) {
                return new self('list', 1);
            }
        }
        return new self('object');
    }

    public function __toString(): string
    {
        return $this->name . str_repeat('[]', $this->depth);
    }
}
