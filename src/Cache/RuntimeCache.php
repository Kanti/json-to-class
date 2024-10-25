<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Cache;

use RuntimeException;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\Mapper\Exception\MapperExceptionInterface;
use Kanti\JsonToClass\Schema\NamedSchema;
use Throwable;
use WeakMap;

use function array_values;
use function get_debug_type;

/**
 * @internal this class is not part of the public API
 * This class helps with resting the cache between multiple runs
 */
final class RuntimeCache
{
    /** @var array<class-string, array<string, string>> */
    private static array $propertyMapping;

    /** @var WeakMap<object, array<string, MapperExceptionInterface>> */
    private static WeakMap $warnings;

    /** @var array<class-string, list<Property>> */
    private array $properties = [];

    /** @var array<class-string, NamedSchema> */
    private array $schema = [];


    /**
     * @param class-string $className
     */
    public function setClassProperties(string $className, Property ...$properties): void
    {
        $this->properties[$className] = array_values($properties);
    }

    /**
     * @param class-string $className
     * @return list<Property>
     */
    public function getClassProperties(string $className): array
    {
        return $this->properties[$className] ?? [];
    }

    /**
     * @param class-string $className
     */
    public function setClassSchema(string $className, NamedSchema $schema): void
    {
        $this->schema[$className] = $schema;
    }

    /**
     * @param class-string $className
     */
    public function getClassSchema(string $className): ?NamedSchema
    {
        return $this->schema[$className] ?? null;
    }

    /**
     * @param class-string $className
     * @param array<string, string> $mapping [propertyName => dataKey]
     */
    public static function setPropertyMapping(string $className, array $mapping): void
    {
        self::$propertyMapping[$className] = $mapping;
    }

    /**
     * @param class-string $className
     * @return array<string, string>
     */
    public static function getPropertyMapping(string $className): array
    {
        return self::$propertyMapping[$className] ?? [];
    }

    public static function addWarning(object $instance, string $key, MapperExceptionInterface $value): void
    {
        self::$warnings ??= new WeakMap();
        self::$warnings[$instance] ??= [];
        self::$warnings[$instance][$key] = $value;
    }

    public static function throwWarning(object $instance, string $key, ?Throwable $fallback = null): never
    {
        throw self::$warnings[$instance][$key]
            ?? $fallback
            ?? new RuntimeException('No warning found for ' . get_debug_type($instance) . '::$' . $key);
    }
}
