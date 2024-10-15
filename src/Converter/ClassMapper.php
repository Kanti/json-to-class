<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Converter;

use AllowDynamicProperties;
use InvalidArgumentException;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Dto\DataTrait;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\Helpers\SH;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use stdClass;

use function assert;

final readonly class ClassMapper
{
    public function __construct(
        private LoggerInterface $logger,
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
    public function map(string $className, array|stdClass $data, Config $config, string $path = ''): object
    {
        if (interface_exists($className, false) && class_exists($className . '_Implementation', false)) {
            $initialClassName = $className;
            $className .= '_Implementation';

            if (!is_a($className, $initialClassName, true)) {
                throw new InvalidArgumentException(sprintf('Class %s does not implement %s %s', $className, $initialClassName, $path));
            }

            if (!DevelopmentCodeCreator::isDevelopmentDto($className)) {
                throw new InvalidArgumentException(sprintf('Class %s must implement %s %s', $className, DataTrait::class, $path));
            }
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist %s', $className, $path));
        }

        if (is_array($data) && array_is_list($data)) {
            throw new InvalidArgumentException(sprintf('Data must be an associative array %s', $path));
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
            $classProperties = DevelopmentCodeCreator::getClassProperties($className);
        }

        $properties = $this->convertProperties($classProperties, $config, $data, $className, $path);

        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $allowsDynamicProperties = (bool)$reflectionClass->getAttributes(AllowDynamicProperties::class);
        foreach ($properties as $key => $value) {
            if ($allowsDynamicProperties) {
                $instance->{$key} = $value;
                continue;
            }

            $reflectionClass->getProperty($key)->setValue($instance, $value);
        }

        return $instance;
    }

    private function convertType(PossibleConvertTargets $possibleTypes, mixed $param, Config $config, string $path): mixed
    {
        $sourceType = Type::fromData($param);

        $type = $possibleTypes->getMatch($sourceType);
        if (!$type) {
            throw new TypesDoNotMatchException($possibleTypes, $sourceType, $path);
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
     * @param list<Property|ReflectionProperty> $properties
     * @param array<mixed>|stdClass $data
     * @return array<string, mixed>
     */
    private function convertProperties(array $properties, Config $config, array|stdClass $data, string $className, string $path): array
    {
        $array = (array)$data;

        $args = [];
        foreach ($properties as $property) {
            $possibleTypes = PossibleConvertTargets::fromParameter($property);
            $dataKey = $property->getName();
            $parameterName = $dataKey;
            if (str_starts_with($parameterName, '_') && $config->onInvalidCharacterProperties === OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE) {
                $dataKey = substr($parameterName, 1);
            }

            if (!array_key_exists($dataKey, $array)) {
                if ($property->hasDefaultValue()) {
                    $args[$parameterName] = $property->getDefaultValue();
                    continue;
                }

                throw new InvalidArgumentException(sprintf('Parameter %s->%s is missing in data %s', $className, $parameterName, $path));
            }

            $args[$parameterName] = $this->convertType($possibleTypes, $array[$dataKey] ?? null, $config, $path . '.' . $dataKey);
        }

        return $args;
    }
}
