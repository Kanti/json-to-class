<?php

declare(strict_types=1);

namespace Kanti\JsonToClass;

use Kanti\JsonToClass\Code\CodeGenerator;
use Kanti\JsonToClass\Code\FileWriter;
use Kanti\JsonToClass\Transformer\Transformer;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Kanti\JsonToClass\Schema\SchemaFromDataGenerator;
use RuntimeException;

final class DevelopmentConverter implements Converter
{
    public function __construct(
        private readonly ProductionConverter $productionConverter = new ProductionConverter(),
        private readonly SchemaFromDataGenerator $schemaFromDataGenerator = new SchemaFromDataGenerator(),
        private readonly CodeGenerator $codeGenerator = new CodeGenerator(),
        private readonly FileWriter $codeWriter = new FileWriter(),
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
        if (!array_is_list($data)) {
            throw new RuntimeException('Data is assoc array, but expected a list (did you mean to use `->convert()` ?)');
        }

        $class = new FullyQualifiedClassName($className);

        $schemaFromData = $this->schemaFromDataGenerator->generate($data);
//        $schemaFromClass = $this->schemaFromClassGenerator->generate($class);
        // generate Schema:
        $classes = $this->codeGenerator->fromSchema($class, $schemaFromData);
        // generate Schema:
        $changes = $this->codeWriter->writeIfNeeded($classes);
        if ($changes) {
            throw new RuntimeException('Schema Changed, needed to write php files. You need to restart the process!!!');
        }

        return $this->productionConverter->convertList($className, $data, $transformer ?? $this->transformer);
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
        if (array_is_list($data)) {
            throw new RuntimeException('Data is a list, but expected an object (did you mean to use `->convertList()` ?)');
        }

        $class = new FullyQualifiedClassName($className);

        $schemaFromData = $this->schemaFromDataGenerator->generate($data);
//        $schemaFromClass = $this->schemaFromClassGenerator->generate($class);
        // generate Schema:
        $classes = $this->codeGenerator->fromSchema($class, $schemaFromData);
        // generate Schema:
        $changes = $this->codeWriter->writeIfNeeded($classes);
        if ($changes) {
            throw new RuntimeException('Schema Changed, needed to write php files. You need to restart the process!!!');
        }

        return $this->productionConverter->convert($className, $data, $transformer ?? $this->transformer);
    }
}
