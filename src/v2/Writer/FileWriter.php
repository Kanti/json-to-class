<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Writer;

use Kanti\JsonToClass\v2\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;

final readonly class FileWriter
{
    public function __construct(
        private ClassLocator $classLocator,
        private FileSystemInterface $fileSystem,
    ) {
    }

    /**
     * @param array<string, string> $classes
     */
    public function writeIfNeeded(array $classes): bool
    {
        $needsRestart = false;
        foreach ($classes as $className => $content) {
            $location = $this->classLocator->getFileLocation($className);

            $oldContent = $this->fileSystem->readContentIfExists($location);
            if ($oldContent === $content) {
                // no change, continue with next class
                continue;
            }

            $this->fileSystem->writeContent($location, $content);

            if (class_exists($className, false)) {
                // we already have the class loaded, so we would need to reload it, but we can't (no monkey patching in PHP)
                $needsRestart = true;
                continue;
            }

            $this->fileSystem->requireFile($location); // load if not loaded already (composer autoloader ignores the new file)
        }

        return $needsRestart;
    }
}
