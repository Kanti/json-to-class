<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper;

use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Converter\PossibleConvertTargets;
use Kanti\JsonToClass\Dto\Property;
use ReflectionProperty;

final readonly class MappingProperty
{
    public function __construct(private Property|ReflectionProperty $property)
    {
    }

    public static function from(Property|ReflectionProperty $property): MappingProperty
    {
        return new MappingProperty($property);
    }

    public function getPossibleTypes(): PossibleConvertTargets
    {
        return PossibleConvertTargets::fromParameter($this->property);
    }

    public function getDataKey(): string
    {
        $property = $this->property;
        if ($property instanceof Property) {
            return $property->getDataKey();
        }

        $reflectionAttribute = $property->getAttributes(Key::class)[0] ?? null;
        return $reflectionAttribute?->newInstance()->key ?? $this->getName();
    }

    public function getName(): string
    {
        return $this->property->getName();
    }

    public function hasDefaultValue(): bool
    {
        if ($this->property instanceof Property) {
            return false;
        }

        return $this->property->hasDefaultValue();
    }
}
