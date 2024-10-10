<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\PhpFile;

final readonly class CodeCreator
{
    public function __construct(
        private PhpFileUpdater $phpFileUpdater,
        private ClassLocator $classLocator,
    ) {
    }

    /**
     * @return array<class-string, string>
     */
    public function createFiles(NamedSchema $schema): array
    {
        if ($schema->basicTypes) {
            throw new Exception('Basic types not supported at this level ' . json_encode($schema, JSON_THROW_ON_ERROR));
        }

        $classes = $this->flatten($schema);
        $result = [];
        foreach ($classes as $className => $classSchema) {
            $file = $this->classLocator->gePhpFileForClass($className) ?? new PhpFile();
            $fileString = $this->phpFileUpdater->updateFile($schema->className, $classSchema, $file);
            $result[$className] = $fileString;
        }

        ksort($result);
        uksort($result, fn($a, $b): int => strlen($a) <=> strlen($b));
        return $result;
    }

    /**
     * @return array<class-string, NamedSchema>
     */
    private function flatten(NamedSchema $schema): array
    {
        $classes = [];

        if ($schema->listElement) {
            $classes = $this->flatten($schema->listElement);
        }

        if ($schema->properties === null) {
            return $classes;
        }

        $classes[$schema->className] = $schema;

        foreach ($schema->properties as $property) {
            $classes = [...$classes, ...$this->flatten($property)];
        }

        return $classes;
    }
}
