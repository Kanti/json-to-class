<?php

declare(strict_types=1);

namespace Kanti\JsonToClass;

use Kanti\JsonToClass\Transformer\Transformer;
use RuntimeException;

final class ProductionConverter implements Converter
{
    public function __construct(
        private Transformer $transformer = new Transformer(),
    ) {}

    public function setTransformer(Transformer $transformer): Converter
    {
        $this->transformer = $transformer;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     *
     * @return T
     */
    public function convert(string $className, array $data, ?Transformer $transformer = null): object
    {
        if (!class_exists($className)) {
            throw new RuntimeException('Class not found: ' . $className . ' did you forget to run the code generator?');
        }
        if (array_is_list($data)) {
            throw new RuntimeException(
                'Data is a list, but expected an object (did you mean to use `->convertList()` ?)',
            );
        }

        return $className::from($data, $transformer ?? $this->transformer);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param list<array<string, mixed>> $data
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
            function ($item) use ($className, $transformer) {
                return $this->convert($className, $item, $transformer);
            },
            $data,
        );
    }
}
