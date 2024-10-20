<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper;

use AllowDynamicProperties;
use InvalidArgumentException;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Cache\RuntimeCache;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Converter\PossibleConvertTargets;
use Kanti\JsonToClass\Dto\KeepDefaultValue;
use Kanti\JsonToClass\Dto\MakeUninitialized;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\Helpers\SH;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

use function array_map;
use function assert;
use function is_array;

final readonly class ClassMapper
{
    public function __construct(
        private LoggerInterface $logger,
        private RuntimeCache $cache,
    ) {
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed>|stdClass $data
     * @phpstan-param array<mixed>|stdClass $data
     *
     * @return T
     */
    public function map(string $className, array|stdClass $data, Config $config, string $path = '$'): object
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist %s', $className, $path));
        }

        if (is_array($data) && array_is_list($data)) {
            throw new InvalidArgumentException(sprintf('Data must be an associative array or stdclass, list is not allowed %s', $path));
        }

        $data = (array)$data;

        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        if ($constructor) {
            $this->logger->warning('Class ' . $className . ' has a constructor. This is not supported, it will not be called');
        }

        /** @var list<ReflectionProperty|Property> $classProperties */
        $classProperties = $reflectionClass->getProperties();
        if (DevelopmentCodeCreator::isDevelopmentDto($className)) {
            $classProperties = $this->cache->getClassProperties($className);
        }

        $classProperties = array_map(MappingProperty::from(...), $classProperties);

        $properties = $this->convertProperties($classProperties, $config, $data, $path);

        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $allowsDynamicProperties = (bool)$reflectionClass->getAttributes(AllowDynamicProperties::class);
        foreach ($properties as $key => $value) {
            if ($value instanceof KeepDefaultValue) {
                // do nothing
                continue;
            }

            if ($value instanceof MakeUninitialized) {
                $this->unsetFromObject($instance, $key);
                continue;
            }

            if ($allowsDynamicProperties && !$reflectionClass->hasProperty($key)) {
                $instance->{$key} = $value;
                continue;
            }

            $reflectionClass->getProperty($key)->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * @param list<MappingProperty> $properties
     * @param array<mixed>|stdClass $data
     * @return array<string, mixed>
     */
    private function convertProperties(array $properties, Config $config, array|stdClass $data, string $path): array
    {
        $array = (array)$data;

        $args = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $dataKey = $property->getDataKey();

            if (!array_key_exists($dataKey, $array)) {
                if ($property->hasDefaultValue()) {
                    $args[$propertyName] = KeepDefaultValue::KeepDefaultValue;
                    continue;
                }

                // TODO Config
                $args[$propertyName] = MakeUninitialized::MakeUninitialized;
                continue;
            }

            $possibleTypes = $property->getPossibleTypes();
            $args[$propertyName] = $this->convertType($possibleTypes, $array[$dataKey] ?? null, $config, $path . '.' . $dataKey);
        }

        return $args;
    }

    private function convertType(PossibleConvertTargets $possibleTypes, mixed $param, Config $config, string $path): mixed
    {
        $sourceType = Type::fromData($param);

        $type = $possibleTypes->getMatch($sourceType);
        if (!$type) {
            return MakeUninitialized::MakeUninitialized;
            // TODO CONFIG
//            throw new TypesDoNotMatchException($possibleTypes, $sourceType, $path);
        }

        if ($type->isBasicType()) {
            return $param;
        }

        assert(is_array($param) || $param instanceof stdClass, 'This should be an array or stdClass at this point in the code');

        if ($type->isArray()) {
            $result = [];
            foreach ((array)$param as $key => $value) {
                $result[$key] = $this->convertType($possibleTypes->unpackOnce(), $value, $config, $path . '.' . $key);
            }

            return $result;
        }

        return $this->map(SH::classString($type->name), $param, $config, $path);
    }

    /**
     * this function can be used to unset private, protected or readonly properties from an object
     */
    private function unsetFromObject(object $object, string ...$names): void
    {
        (function () use ($names): void {
            foreach ($names as $name) {
                unset($this->{$name});
            }
        })->call($object);
    }
}
