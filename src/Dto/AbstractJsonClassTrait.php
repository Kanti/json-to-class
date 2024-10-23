<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use AllowDynamicProperties;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Cache\RuntimeCache;
use ReflectionClass;
use ReflectionProperty;

use function array_keys;
use function assert;

/**
 * @internal
 */
trait AbstractJsonClassTrait
{
    /**
     * this will only work if the property was uninitialized and additionally unset
     */
    public function __get(string $name): mixed
    {
        // property_exists is even true if the property was unset
        if (property_exists($this, $name)) {
            if ((new ReflectionProperty($this, $name))->getType()?->allowsNull()) {
                return $this->{$name} ?? null; // mute error Typed property %s::$%s must not be accessed before initialization
            }

            return $this->{$name}; // throws error Typed property %s::$%s must not be accessed before initialization
        }

        if ((new ReflectionClass($this))->getAttributes(AllowDynamicProperties::class)) {
            return $this->{$name} ?? null;
        }

        return $this->{$name}; // triggers Warning: Undefined property: %s::$%s in %s on line %d
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $schemaProperties = RuntimeCache::getPropertyMapping(static::class);
        if (!$schemaProperties) {
            $schemaProperties = [];
            $reflectionClass = new ReflectionClass($this);
            foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $attributeKey = ($property->getAttributes(Key::class)[0] ?? null)?->newInstance()->key;
                $name = $property->getName();
                $schemaProperties[$name] = $attributeKey ?? $name;
            }

            RuntimeCache::setPropertyMapping(static::class, $schemaProperties);
        }

        assert(count($schemaProperties) > 0, 'No properties found');

        $result = [];
        foreach ((array)$this as $propertyName => $value) {
            $result[$schemaProperties[$propertyName] ?? $propertyName] = $value;
        }

        return $result;
    }
}
