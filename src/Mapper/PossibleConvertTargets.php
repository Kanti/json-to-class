<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper;

use InvalidArgumentException;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\Dto\Type;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Stringable;

use function assert;

final readonly class PossibleConvertTargets implements Stringable
{
    /**
     * @param list<Type> $types
     */
    public function __construct(
        public array $types,
    ) {
        array_map(fn(Type $type): int => 0, $this->types); // check types
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

    public static function fromParameter(ReflectionProperty|Property $property): self
    {
        if ($property instanceof Property) {
            return new self($property->types);
        }

        $attribute = $property->getAttributes(Types::class)[0] ?? null;
        if ($attribute) {
            return new self($attribute->newInstance()->types);
        }

        // if no attribute is set, it is never a list

        $type = $property->getType() ?? throw new InvalidArgumentException("Type cannot be null");

        if ($type instanceof ReflectionIntersectionType) {
            throw new InvalidArgumentException("Intersection types are not supported");
        }

        if ($type instanceof ReflectionNamedType) {
            $types = [Type::from($type->getName())];
            if ($type->allowsNull()) {
                $types[] = Type::from('null');
            }

            return new self($types);
        }

        assert($type instanceof ReflectionUnionType);

        $types = [];

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

            if ($type->isEmptyArray()) {
                continue;
            }

            $types[] = $type->unpackOnce();
        }

        return new self($types);
    }

    public function __toString(): string
    {
        return implode('|', $this->types);
    }
}
