<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Code;

use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\Dto\FullyQualifiedClassName;
use Nette\PhpGenerator\PsrPrinter;

final readonly class FileWriter
{
    private ClassLoader $classLoader;

    public function __construct(
        ?ClassLoader $classLoader = null,
        private PsrPrinter $printer = new PsrPrinter(),
    ) {
        $this->classLoader = $classLoader ?? require __DIR__ . '/../../vendor/autoload.php';
    }

    public function writeIfNeeded(Classes $classes): bool
    {
        $classChanged = false;
        foreach ($classes as $className => $class) {
            $content = $this->printer->printFile($class['phpFile']);

            $location = $this->getFileLocation($class['class']);

            $oldContent = null;
            if (file_exists($location)) {
                $oldContent = file_get_contents($location);
            }
            if ($oldContent === $content) {
                // no change, continue
                continue;
            }
            $this->writeContent($location, $content);

            if (class_exists($className, false)) {
                // we already have the class loaded, so we would need to reload it, but we can't (no monkey patching in PHP)
                $classChanged = true;
                continue;
            }

            require_once $location; // load if not loaded already (composer autoloader ignores the new file)
        }
        return $classChanged;
    }

    private function getFileLocation(FullyQualifiedClassName $class): string
    {
        $psr4 = $this->classLoader->getPrefixesPsr4();

        // already exists:
        $file = $this->classLoader->findFile((string)$class);
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
                    throw new \Exception('Multiple possible paths found'); // TODO do we want an exception here?
                }
                $path = $possiblePaths[0] ?? throw new \Exception('Path not found');

                $missingParts = array_slice($originalNamespaceParts, count($namespaceParts));
                foreach ($missingParts as $missingPart) {
                    $path .= '/' . $missingPart;
                }
                return $path . '/' . $class->className . '.php';
            }
        } while (array_pop($namespaceParts));

        throw new \Exception('Path not found no psr4 path found in composer autoload for ' . (string)$class);
    }

    private function writeContent(string $location, string $content): void
    {
        $directory = dirname($location);
        if (!is_dir($directory)) {
            if (!mkdir($directory, recursive: true) && !is_dir($directory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }
        }

        file_put_contents($location, $content);
    }

}
