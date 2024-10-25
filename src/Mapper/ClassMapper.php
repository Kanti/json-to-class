<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper;

use AllowDynamicProperties;
use Exception;
use InvalidArgumentException;
use Kanti\JsonToClass\Cache\RuntimeCache;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\StrictConfig;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Mapper\Exception\MapperExceptionInterface;
use Kanti\JsonToClass\Mapper\Exception\MissingDataException;
use Kanti\JsonToClass\Mapper\Exception\MissingDataKeepDefaultValueException;
use Kanti\JsonToClass\Mapper\Exception\NoPossibleTypesException;
use Kanti\JsonToClass\Mapper\Exception\TypesDoNotMatchException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use stdClass;
use Throwable;

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
            if ($value instanceof MapperExceptionInterface) {
                // TODO config handling: (throwOnUse vs throwOnConvert)
                if ($config instanceof StrictConfig) {
                    throw $value;
                }

                RuntimeCache::addWarning($instance, $key, $value);

                $this->logger->warning($value->getMessage());
                if ($value instanceof MissingDataKeepDefaultValueException) {
                    continue;
                }

                if (
                    $value instanceof TypesDoNotMatchException
                    || $value instanceof MissingDataException
                    || $value instanceof NoPossibleTypesException
                ) {
                    $this->unsetFromObject($instance, $key);
                    continue;
                }
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
            $possibleTypes = $property->getPossibleTypes();

            if (!array_key_exists($dataKey, $array)) {
                if ($property->hasDefaultValue()) {
                    $args[$propertyName] = new MissingDataKeepDefaultValueException($possibleTypes, $path . '.' . $dataKey);
                    continue;
                }

                $args[$propertyName] = new MissingDataException($possibleTypes, $path . '.' . $dataKey);
                continue;
            }


            try {
                $convertedType = $this->convertType($possibleTypes, $array[$dataKey] ?? null, $config, $path . '.' . $dataKey);
            } catch (MapperExceptionInterface $exception) {
                $convertedType = $exception;
            }

            $args[$propertyName] = $convertedType;
        }

        return $args;
    }

    /**
     * @throws NoPossibleTypesException
     * @throws TypesDoNotMatchException
     * @throws RuntimeException
     */
    private function convertType(PossibleConvertTargets $possibleTypes, mixed $data, Config $config, string $path): mixed
    {
        $sourceType = Type::fromData($data);

        if (!$possibleTypes->types) {
            throw new NoPossibleTypesException($sourceType, $path, $data);
        }

        $type = $possibleTypes->getMatch($sourceType);
        if (!$type) {
            throw new TypesDoNotMatchException($possibleTypes, $sourceType, $path, $data);
        }

        if ($type->isBasicType()) {
            return $data;
        }

        assert(
            is_array($data) || $data instanceof stdClass,
            'This should be an array or stdClass at this point in the code',
        );

        if ($type->isArray()) {
            $result = [];
            foreach ((array)$data as $key => $value) {
                try {
                    $result[$key] = $this->convertType($possibleTypes->unpackOnce(), $value, $config, $path . '.' . $key);
                } catch (Throwable $throwable) {
                    if ($throwable instanceof MapperExceptionInterface) {
                        throw $throwable;
                    }

                    throw new RuntimeException('Error at ' . $path . '.' . $key . ': ' . $throwable->getMessage(), 0, $throwable);
                }
            }

            return $result;
        }

        return $this->map(F::classString($type->name), $data, $config, $path);
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
