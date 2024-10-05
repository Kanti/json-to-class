<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Converter;

use InvalidArgumentException;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Dto\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Dto\DataTrait;
use Kanti\JsonToClass\Dto\Parameter;
use Kanti\JsonToClass\Dto\Type;
use ReflectionClass;
use ReflectionParameter;
use stdClass;

use function Safe\class_uses;

final class ClassMapper
{
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
        if (!$constructor) {
            throw new InvalidArgumentException(sprintf('Class %s does not have a constructor, but it is required %s', $className, $path));
        }

        /** @var list<ReflectionParameter|Parameter> $constructorParameters */
        $constructorParameters = $constructor->getParameters();

        if (DevelopmentCodeCreator::isDevelopmentDto($className)) {
            $constructorParameters = $className::getClassParameters();
        }

        $args = [];
        foreach ($constructorParameters as $parameter) {
            $possibleTypes = PossibleConvertTargets::fromParameter($parameter);
            $dataKey = $parameter->getName();
            $parameterName = $dataKey;
            if (str_starts_with($parameterName, '_') && $config->onInvalidCharacterProperties === OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE) {
                $dataKey = substr($parameterName, 1);
            }

            if (!array_key_exists($dataKey, $data)) {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }

                throw new InvalidArgumentException(sprintf('Parameter %s->%s is missing in data %s', $className, $parameterName, $path));
            }

            $args[$parameterName] = $this->convertType($possibleTypes, $data[$dataKey] ?? null, $config, $path . '.' . $dataKey);
        }

        return new $className(...$args);
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

        /** @var class-string $className */
        $className = $type->name;
        return $this->map($className, $param, $config, $path);
    }
}
