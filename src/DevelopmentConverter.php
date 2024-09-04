<?php

declare(strict_types=1);

namespace Kanti\JsonToClass;

use Kanti\JsonToClass\Code\CodeGenerator;
use Kanti\JsonToClass\Code\FileWriter;
use Kanti\JsonToClass\Transformer\Transformer;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Kanti\JsonToClass\Schema\SchemaFromClassGenerator;
use Kanti\JsonToClass\Schema\SchemaFromDataGenerator;
use RuntimeException;

final readonly class DevelopmentConverter implements Converter
{
    public function __construct(
        private ProductionConverter $productionConverter = new ProductionConverter(),
        private SchemaFromDataGenerator $schemaFromDataGenerator = new SchemaFromDataGenerator(),
        private SchemaFromClassGenerator $schemaFromClassGenerator = new SchemaFromClassGenerator(),
        private CodeGenerator $codeGenerator = new CodeGenerator(),
        private FileWriter $codeWriter = new FileWriter(),
        private Transformer $transformer = new Transformer(),
    ) {}

    public function setTransformer(Transformer $transformer): Converter
    {
        $this->transformer = $transformer;
    }

    public static function createInstance(bool $autoCreate = true): Converter
    {
        if (!$autoCreate) {
            return new ProductionConverter();
        }
        return new self(
            new ProductionConverter(),
            new SchemaFromDataGenerator(),
            new SchemaFromClassGenerator(),
            new CodeGenerator(),
            new FileWriter(),
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed> $data
     *
     * @return T
     */
    public function convert(string $className, array $data, ?Transformer $transformer = null): object
    {
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

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param list<array<string, mixed>> $data
     *
     * @return list<T>
     */
    public function convertList(string $className, array $data, ?Transformer $transformer = null): array
    {
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
}
