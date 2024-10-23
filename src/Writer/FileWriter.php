<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Writer;

use Kanti\JsonToClass\ClassCreator\ShouldRestartException;
use Kanti\JsonToClass\CodeCreator\DevelopmentCodeCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\Enums\ShouldCreateDevelopmentClasses;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;

use function implode;
use function sprintf;

use const PHP_EOL;

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
        $locationsWrittenTo = [];
        foreach ($classes as $className => $content) {
            $location = $this->classLocator->getFileLocation($className);

            $oldContent = $this->fileSystem->readContentIfExists($location);
            if ($oldContent === $content) {
                // no change, continue with next class
                continue;
            }

            $this->fileSystem->writeContent($location, $content);
            $locationsWrittenTo[] = $location;

            if (DevelopmentCodeCreator::isDevelopmentDto($className)) {
                continue;
            }

            if (class_exists($className, false)) {
                $needsRestart[] = $className;
            }
        }

        if ($needsRestart) {
            $message = sprintf('Class %s already exists and cannot be reloaded', implode(', ', $needsRestart));
            $message .= PHP_EOL . 'Please restart the application to reload the classes';
            $message .= PHP_EOL . 'make sure you do not load the classes yourself, that would prevent the monkey patching';
            throw new ShouldRestartException($message);
        }

        return $locationsWrittenTo;
    }
}
