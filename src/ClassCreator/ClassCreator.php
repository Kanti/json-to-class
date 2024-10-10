<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\ClassCreator;

use InvalidArgumentException;
use Kanti\JsonToClass\CodeCreator\CodeCreator;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\AppendSchema;
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
        private DevelopmentCodeCreator $developmentCodeCreator,
        private SchemaMerger $schemaMerger,
        private CodeCreator $codeCreator,
        private FileWriter $fileWriter,
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
            throw new InvalidArgumentException('Class name must contain namespace');
        }

        $schema = $this->schemaFromDataCreator->fromData($data, $config);
        $schema = NamedSchema::fromSchema($className, $schema);

        if ($config->appendSchema === AppendSchema::APPEND) {
            $schemaFromClass = $this->schemaFromClassCreator->fromClasses($className);
            $schema = $this->schemaMerger->merge($schema, $schemaFromClass);
        }

        $files = $this->codeCreator->createFiles($schema->getFirstNonListChild());

        if ($restartReasons = $this->fileWriter->writeIfNeeded($files)) {
            $message = sprintf('Class %s already exists and cannot be reloaded', implode(', ', $restartReasons));
            $message .= PHP_EOL . 'Please restart the application to reload the classes';
            $message .= PHP_EOL . 'make sure you do not load the classes yourself, that would prevent the monkey patching';
            throw new ShouldRestartException($message);
        }

        $this->developmentCodeCreator->createDevelopmentClasses($schema);
    }
}
