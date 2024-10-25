<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use AllowDynamicProperties;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Cache\RuntimeCache;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

use function array_keys;
use function assert;
use function sprintf;

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

            $fallback = new RuntimeException(sprintf('Typed property %s::$%s must not be accessed before initialization', $this::class, $name));
            RuntimeCache::throwWarning($this, $name, $fallback);
        }

        if ((new ReflectionClass($this))->getAttributes(AllowDynamicProperties::class)) {
            return $this->{$name} ?? null;
        }

        $fallback = new RuntimeException(sprintf('Undefined property %s::$%s', $this::class, $name));
        RuntimeCache::throwWarning($this, $name, $fallback);
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
