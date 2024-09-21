<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\ClassCreator;

use InvalidArgumentException;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Dto\AppendSchema;
use Kanti\JsonToClass\Schema\NamedSchema;
use Kanti\JsonToClass\Schema\SchemaFromClassCreator;
use Kanti\JsonToClass\Schema\SchemaFromDataCreator;
use Kanti\JsonToClass\Schema\SchemaMerger;
use Kanti\JsonToClass\Writer\FileWriter;
use stdClass;

final readonly class ClassCreator
{
    public function __construct(
        private SchemaFromClassCreator $schemaFromClassCreator,
        private SchemaFromDataCreator $schemaFromDataCreator,
        private SchemaMerger $schemaMerger,
        private CodeCreator $codeCreator,
        private FileWriter $fileWriter,
    ) {
    }

    /**
     * @param array<mixed>|stdClass $data
     */
    public function createClasses(
        string $className,
        array|stdClass $data,
        Config $config,
    ): void {
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace');
        }

        $schema = $this->schemaFromDataCreator->fromData($data);
        $schema = NamedSchema::fromSchema($className, $schema);

        if ($config->appendSchema === AppendSchema::APPEND) {
            $schemaFromClass = $this->schemaFromClassCreator->fromClasses($className);
            $schema = $this->schemaMerger->merge($schema, $schemaFromClass);
        }

        while ($schema->isOnlyAList()) {
            // if the first level(s) are a list, than ignore that level for class creation
            $schema = $schema->listElement;
        }

        $files = $this->codeCreator->createFiles($schema);

        $this->fileWriter->writeIfNeeded($files);
    }
}
