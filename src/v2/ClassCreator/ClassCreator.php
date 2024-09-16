<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\ClassCreator;

use Kanti\JsonToClass\v2\CodeCreator\CodeCreator;
use Kanti\JsonToClass\v2\Config\Config;
use Kanti\JsonToClass\v2\Config\Dto\AppendSchema;
use Kanti\JsonToClass\v2\Schema\NamedSchema;
use Kanti\JsonToClass\v2\Schema\SchemaFromClassCreator;
use Kanti\JsonToClass\v2\Schema\SchemaFromDataCreatorInterface;
use Kanti\JsonToClass\v2\Writer\FileWriter;
use stdClass;

final readonly class ClassCreator implements ClassCreatorInterface
{
    public function __construct(
        private SchemaFromClassCreator $schemaFromClassCreator,
        private SchemaFromDataCreatorInterface $schemaFromDataCreator,
        private CodeCreator $codeCreator,
        private FileWriter $fileWriter,
    ) {
    }

    public function createClasses(
        string $className,
        array|stdClass $data,
        Config $config,
    ): void {
        if (!str_contains($className, '\\')) {
            throw new \InvalidArgumentException('Class name must contain namespace');
        }

        $schema = $this->schemaFromDataCreator->fromData($data);
        $schema = NamedSchema::fromSchema($className, $schema);

        if ($config->appendSchema === AppendSchema::APPEND) {
            $schema = $this->schemaFromClassCreator->fromClasses($className);
        }
        while ($schema->isOnlyAList()) {
            // if the first level(s) are a list, than ignore that level for class creation
            $schema = $schema->listElement;
        }
        $files = $this->codeCreator->createFiles($schema);

        $this->fileWriter->writeIfNeeded($files);
    }
}
