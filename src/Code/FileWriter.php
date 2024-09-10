<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Code;

use Composer\Autoload\ClassLoader;
use Exception;
use Kanti\JsonToClass\Abstraction\FileSystem;
use Kanti\JsonToClass\Abstraction\FileSystemInterface;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;

final readonly class FileWriter
{
    private ClassLoader $classLoader;

    public function __construct(
        ?ClassLoader $classLoader = null,
        private FileSystemInterface $fileSystem = new FileSystem(),
    ) {
        $this->classLoader = $classLoader ?? require __DIR__ . '/../../vendor/autoload.php';
    }

    public function writeIfNeeded(Classes $classes): bool
    {
        $needsRestart = false;
        foreach ($classes as $className => $content) {
            $location = $this->getFileLocation($className);

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

    private function getFileLocation(string $className): string
    {
        $class = new FullyQualifiedClassName($className);
        $psr4 = $this->classLoader->getPrefixesPsr4();

        // find location of the existing file, if present
        $file = $this->classLoader->findFile($className);
        if ($file) {
            return $file;
        }

        $originalNamespaceParts = explode('\\', $class->namespace);
        $namespaceParts = $originalNamespaceParts;

        do {
            $currentNamespacePrefix = implode('\\', $namespaceParts) . '\\';
            if (isset($psr4[$currentNamespacePrefix])) {
                $possiblePaths = $psr4[$currentNamespacePrefix];
                if (count($possiblePaths) > 1) {
                    throw new Exception('Multiple possible paths found'); // TODO do we want an exception here?
                }

                $path = $possiblePaths[0] ?? throw new Exception('Path not found');
                $path = rtrim($path, '/');

                $missingParts = array_slice($originalNamespaceParts, count($namespaceParts));
                foreach ($missingParts as $missingPart) {
                    $path .= '/' . $missingPart;
                }

                return $path . '/' . $class->className . '.php';
            }
        } while (array_pop($namespaceParts));

        throw new Exception('Path not found no psr4 path found in composer autoload for ' . $className);
    }
}
