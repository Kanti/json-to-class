<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Converter;

use Kanti\JsonToClass\v2\Config\Config;
use Kanti\JsonToClass\v2\Dto\Type;
use stdClass;

final class ClassMapper
{
    public function map(string $className, array|stdClass $data, Config $config): object
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class $className does not exist");
        }
        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            throw new \InvalidArgumentException("Class $className does not have a constructor, but it is required");
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
                throw new \InvalidArgumentException("Parameter $parameterName is missing in data");
            }
            $args[$parameterName] = $this->convertType($possibleTypes, $data[$parameterName] ?? null, $config);
        }
        return new $className(...$args);
    }

    private function convertType(PossibleConvertTargets $possibleTypes, mixed $param, Config $config): mixed
    {
        $sourceType = Type::fromData($param);

        $type = $possibleTypes->getMatch($sourceType);
        if (!$type) {
            throw new TypesDoNotMatchException($possibleTypes, $sourceType);
        }

        if ($type->isBasicType()) {
            return $param;
        }

        if ($type->isArray()) {
            return array_map(fn($item) => $this->convertType($possibleTypes->unpackOnce(), $item, $config), $param);
        }

        return $this->map($type->name, $param, new Config());
    }
}
