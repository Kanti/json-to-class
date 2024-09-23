<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\FileSystemAbstraction;

use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\Helpers\ExceptionHelper;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;
use RuntimeException;

final readonly class ClassLocator
{
    public function __construct(
        private ClassLoader $classLoader,
        private FileSystemInterface $fileSystem,
    ) {
    }

    public function getClass(string $className): ClassType
    {
        $location = $this->getFileLocation($className);
        $content = $this->fileSystem->readContent($location);
        $object = PhpFile::fromCode($content)->getClasses()[$className] ?? throw new RuntimeException('Class not found');
        if (!$object instanceof ClassType) {
            throw new RuntimeException('Class ' . $className . ' not found it is a ' . ExceptionHelper::getTypeOfClass($object));
        }

        return $object;
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
                    throw new RuntimeException('Multiple possible paths found'); // TODO do we want an exception here?
                }

                $path = $possiblePaths[0] ?? throw new RuntimeException('Path not found');
                $path = rtrim($path, '/');

                $missingParts = array_slice($originalNamespaceParts, count($namespaceParts));
                foreach ($missingParts as $missingPart) {
                    $path .= '/' . $missingPart;
                }

                return $path . '/' . Helpers::extractShortName($className) . '.php';
            }
        } while (array_pop($namespaceParts));

        throw new RuntimeException('Path not found no psr4 path found in composer autoload for ' . $className);
    }
}
