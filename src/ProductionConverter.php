<?php

declare(strict_types=1);

namespace Kanti\JsonToClass;

use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Schema\SchemaFromDataGenerator;
use Kanti\JsonToClass\Transformer\Transformer;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

final class ProductionConverter implements Converter
{
    public function __construct(
        private Transformer $transformer = new Transformer(),
    ) {
    }

    public function setTransformer(Transformer $transformer): Converter
    {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param list<array<string, mixed>> $data
     * @phpstan-param array<mixed> $data
     *
     * @return list<T>
     */
    public function convertList(string $className, array $data, ?Transformer $transformer = null): array
    {
        if (!class_exists($className)) {
            throw new RuntimeException('Class not found: ' . $className . ' did you forget to run the code generator?');
        }

        if (!array_is_list($data)) {
            throw new RuntimeException(
                'Data is assoc array, but expected a list (did you mean to use `->convert()` ?)',
            );
        }

        return array_map(
            fn(array $item): object => $this->convert($className, $item, $transformer),
            $data,
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $data
     * @phpstan-param array<mixed> $data
     *
     * @return T
     */
    public function convert(string $className, array $data, ?Transformer $transformer = null): object
    {
        $transformer ??= $this->transformer;
        if (!class_exists($className)) {
            throw new RuntimeException('Class not found: ' . $className . ' did you forget to run the code generator?');
        }

        if (array_is_list($data)) {
            throw new RuntimeException(
                'Data is a list, but expected an object (did you mean to use `->convertList()` ?)',
            );
        }

        $class = new ReflectionClass($className);

        $properties = [];

        foreach ($class->getProperties() as $property) {
            $name = $property->getName();
            $properties[$name] = $this->transform($property, $data[$name] ?? null, $transformer);
        }

        return new $className(...$properties);
    }

    private function transform(ReflectionProperty $property, mixed $data, Transformer $transformer): mixed
    {
        $targetTypes = $this->getTypesFromProperty($property);

        return $this->transformType($data, $targetTypes, $transformer);
    }

    public function getTypesFromProperty(ReflectionProperty $property): array
    {
        $attribute = $property->getAttributes(Types::class)[0] ?? null;
        if ($attribute) {
            $newInstance = $attribute->newInstance();
            assert($newInstance instanceof Types);
            return $newInstance->types;
        }
        dd($attribute);
    }

    private function getChildTypes(array $targetTypes): array
    {
        $result = [];
        foreach ($targetTypes as $type) {
            if (!is_array($type)) {
                continue;
            }
            if (count($type) > 1) {
                throw new RuntimeException('Only one type is allowed in child types ' . json_encode($type));
            }
            $result[] = $type[0];
        }
        return $result;
    }

    private function transformType(mixed $data, mixed $targetTypes, Transformer $transformer)
    {
        $targetChildTypes = $this->getChildTypes($targetTypes);
        $sourceType = SchemaFromDataGenerator::getType($data);
        if ($sourceType === 'object') {
            foreach ($targetChildTypes as $target) {
                if (is_string($target) && class_exists($target)) {
                    return $this->convert($target, $data, $transformer);
                }
            }
            throw new RuntimeException('No matching type found');
        }
        if ($sourceType === 'list') {
            return array_map(
                fn(mixed $item): mixed => $this->transformType($item, $targetChildTypes, $transformer),
                $data,
            );
        }
        if (in_array($sourceType, $targetTypes, true)) {
            return $data;
        }
        throw new RuntimeException('No matching type found');
    }
}
