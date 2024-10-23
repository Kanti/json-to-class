<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\ClassCreator;

use InvalidArgumentException;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\AppendSchema;
use Kanti\JsonToClass\Config\Enums\ShouldCreateDevelopmentClasses;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Kanti\JsonToClass\Schema\SchemaFromDataCreator;
use Kanti\JsonToClass\Schema\SchemaMerger;
use Kanti\JsonToClass\Schema\SchemaToNamedSchemaConverter;
use Kanti\JsonToClass\Writer\FileWriter;
use stdClass;

final readonly class ClassCreator
{
    public function __construct(
        private SchemaFromClassCreator $schemaFromClassCreator,
        private SchemaFromDataCreator $schemaFromDataCreator,
        private SchemaToNamedSchemaConverter $schemaToNamedSchemaConverter,
        private DevelopmentCodeCreator $developmentCodeCreator,
        private SchemaMerger $schemaMerger,
        //private SchemaSimplification $schemaSimplification,
        private CodeCreator $codeCreator,
        private FileWriter $fileWriter,
        private FileSystemInterface $fileSystem,
    ) {
    }

    /**
     * @param class-string $className
     * @param array<mixed>|stdClass $data
     */
    public function createClasses(
        string $className,
        array|stdClass $data,
        Config $config,
    ): void {
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException(sprintf('$className must have a namespace ("%s" dose not include \\)', $className));
        }

        $schema = $this->schemaFromDataCreator->fromData($data, $config);
        $schema = $this->schemaToNamedSchemaConverter->convert($className, $schema, null);

        if ($config->appendSchema === AppendSchema::APPEND) {
            $schemaFromClass = $this->schemaFromClassCreator->fromClasses($className);
            $schema = $this->schemaMerger->merge($schema, $schemaFromClass);

            /** @noinspection TypeUnsafeComparisonInspection */
            if ($schemaFromClass == $schema) {
                // we do not need to update the files on disk
//                return; // if the schema is right, it does not mean the files are right ( maybe some changes need to be done that do not effect the schema :/  )
            }
        }

//        $schema = $this->schemaSimplification->simplify($schema);
//        if (!$schema) {
//            throw new InvalidArgumentException(sprintf("Schema is empty for data: %s", json_encode($data)));
//        }

        $files = $this->codeCreator->createFiles($schema->getFirstNonListChild());

        $locationsWritten = $this->fileWriter->writeIfNeeded($files);

        if ($config->shouldCreateDevelopmentClasses === ShouldCreateDevelopmentClasses::YES) {
            $this->developmentCodeCreator->createOrUpdateDevelopmentClasses($schema);
        } else {
            foreach ($locationsWritten as $location) {
                $this->fileSystem->require($location);
            }
        }
    }
}
