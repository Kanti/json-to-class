<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use AllowDynamicProperties;
use ReflectionClass;
use ReflectionProperty;

trait MuteUninitializedPropertyError
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
}
