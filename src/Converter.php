<?php

declare(strict_types=1);

namespace Kanti\JsonToClass;

use Kanti\JsonToClass\Transformer\Transformer;

interface Converter
{
    public function setTransformer(Transformer $transformer): self;

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param list<array<string, mixed>> $data
     * @phpstan-param array<mixed> $data
     *
     * @return list<T>
     */
    public function convertList(string $className, array $data, ?Transformer $transformer = null): array;

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $data
     * @phpstan-param array<mixed> $data
     *
     * @return T
     */
    public function convert(string $className, array $data, ?Transformer $transformer = null): object;
}
