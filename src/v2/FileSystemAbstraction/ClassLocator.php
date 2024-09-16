<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\FileSystemAbstraction;

use Composer\Autoload\ClassLoader;
use Exception;
use Nette\PhpGenerator\Helpers;

final readonly class ClassLocator
{
    public function __construct(private ClassLoader $classLoader)
    {
    }

    public function getFileLocation(string $className): string
    {
        $psr4 = $this->classLoader->getPrefixesPsr4();

        // find location of the existing file, if present
        $file = $this->classLoader->findFile($className);
        if ($file) {
            return $file;
        }

        $originalNamespaceParts = explode('\\', Helpers::extractNamespace($className));
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

                return $path . '/' . Helpers::extractShortName($className) . '.php';
            }
        } while (array_pop($namespaceParts));

        throw new Exception('Path not found no psr4 path found in composer autoload for ' . $className);
    }
}
