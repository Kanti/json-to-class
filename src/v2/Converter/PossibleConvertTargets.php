<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Converter;

use InvalidArgumentException;
use Kanti\JsonToClass\v2\Attribute\Types;
use Kanti\JsonToClass\v2\Dto\Type;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

final readonly class PossibleConvertTargets
{
    /**
     * @param list<Type> $types
     */
    public function __construct(
        public array $types,
    ) {
        array_map(fn(Type $type) => 0, $this->types);
    }

    public function getMatch(Type $type): ?Type
    {
        foreach ($this->types as $possibleType) {
            if ($type->isArray() && $possibleType->isArray()) {
                return $possibleType;
            }
            if ($possibleType->isClass() && $type->isClass()) {
                return $possibleType;
            }
            if ($possibleType->name !== $type->name) {
                continue;
            }
            return $possibleType;
        }
        return null;
    }

    public static function fromReflectionType(ReflectionParameter $parameter): self
    {
        $attribute = $parameter->getAttributes(Types::class)[0] ?? null;
        if ($attribute) {
            return new self($attribute->newInstance()->types);
        }

        // if no attribute is set, it is never a list

        $type = $parameter->getType();
        if (!$type) {
            throw new InvalidArgumentException("Type cannot be null");
        }
        if ($type instanceof ReflectionIntersectionType) {
            throw new InvalidArgumentException("Intersection types are not supported");
        }
        if ($type instanceof ReflectionNamedType) {
            return new self([Type::from($type->getName())]);
        }
        $types = [];
        if (!$type instanceof ReflectionUnionType) {
            throw new InvalidArgumentException("Union types are not supported");
        }
        foreach ($type->getTypes() as $unionType) {
            if (!$unionType instanceof ReflectionNamedType) {
                throw new InvalidArgumentException("Only named types are supported in union types");
            }
            $types[] = Type::from($unionType->getName());
        }
        return new self($types);
    }

    public function unpackOnce(): self
    {
        $types = [];
        foreach ($this->types as $type) {
            if ($type->depth <= 0) {
                continue;
            }
            $types[] = $type->unpackOnce();
        }
        if (!$types) {
            throw new InvalidArgumentException('Max possible depth reached');
        }
        return new self($types);
    }

    public function __toString(): string
    {
        return implode('|', $this->types);
    }
}
