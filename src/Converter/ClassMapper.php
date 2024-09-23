<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Converter;

use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Dto\Type;
use ReflectionClass;
use stdClass;

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
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class %s does not exist %s', $className, $path));
        }

        $data = (array)$data;
        if (array_is_list($data)) {
            throw new InvalidArgumentException(sprintf('Data must be an associative array %s', $path));
        }

        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            throw new InvalidArgumentException(sprintf('Class %s does not have a constructor, but it is required %s', $className, $path));
        }

        $constructorParameters = $constructor->getParameters();
        $args = [];
        foreach ($constructorParameters as $parameter) {
            $possibleTypes = PossibleConvertTargets::fromReflectionType($parameter);

            $parameterName = $parameter->getName();
            if (!array_key_exists($parameterName, $data)) {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }

                throw new InvalidArgumentException(sprintf('Parameter %s is missing in data %s', $parameterName, $path));
            }

            $args[$parameterName] = $this->convertType($possibleTypes, $data[$parameterName] ?? null, $config, $path . '.' . $parameterName);
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

        return $this->map($type->getClassName(), $param, $config, $path);
    }
}
