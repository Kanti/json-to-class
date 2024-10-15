<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Writer;

use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;

final readonly class FileWriter
{
    public function __construct(
        private ClassLocator $classLocator,
        private FileSystemInterface $fileSystem,
    ) {
    }

    /**
     * @param array<class-string, string> $classes
     * @return list<string>
     */
    public function writeIfNeeded(array $classes): array
    {
        $needsRestart = [];
        foreach ($classes as $className => $content) {
            $location = $this->classLocator->getFileLocation($className);

            $oldContent = $this->fileSystem->readContentIfExists($location);
            if ($oldContent === $content) {
                // no change, continue with next class
                continue;
            }

            $this->fileSystem->writeContent($location, $content);

            if (DevelopmentCodeCreator::isDevelopmentDto($className)) {
                continue;
            }

            if (class_exists($className, false)) {
                $needsRestart[] = $className;
            }
        }

        return $needsRestart;
    }
}
