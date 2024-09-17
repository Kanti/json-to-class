<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\FileSystemAbstraction;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;
use RuntimeException;

final readonly class FileSystem implements FileSystemInterface
{
    private string $rootDirectory;

    public function __construct(?string $rootDirectory = null)
    {
        $rootDirectory ??= getcwd() ?: throw new RuntimeException('Could not determine root directory');
        $this->rootDirectory = rtrim($rootDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function requireFile(string $filename): void
    {
        $filename = $this->makePathAbsolute($filename);
        require_once $filename;
    }

    private function makePathAbsolute(string $filename): string
    {
        if (str_starts_with($filename, '/')) {
            return $this->realpathIfPossible($filename);
        }

        // on windows it is possible that the root directory is a drive letter
        if (str_contains($filename, ':')) {
            return $this->realpathIfPossible($filename);
        }

        return $this->realpathIfPossible($this->rootDirectory . $filename);
    }

    public function writeContent(string $filename, string $content): void
    {
        $filename = $this->makePathAbsolute($filename);
        $directory = dirname($filename);
        if (!is_dir($directory) && (!@mkdir($directory, recursive: true) && !is_dir($directory))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        file_put_contents($filename, $content);
    }

    public function readContent(string $filename): string
    {
        $filename = $this->makePathAbsolute($filename);
        $result = $this->readContentIfExists($filename);
        if ($result === null) {
            throw new RuntimeException('File does not exist: ' . $filename);
        }

        return $result;
    }

    public function readContentIfExists(string $filename): ?string
    {
        $filename = $this->makePathAbsolute($filename);
        if (!file_exists($filename)) {
            return null;
        }

        $contents = file_get_contents($filename);
        assert($contents !== false, 'File exists, so content should be readable');
        return $contents;
    }

    public function listFiles(string $directory, string $extension, bool $recursive = true): array
    {
        $directory = $this->makePathAbsolute($directory);
        if (!$recursive) {
            return glob($directory . '/*.' . $extension) ?: [];
        }

        $files = [];
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        $regex = new RegexIterator($iterator, '/^.+\.' . $extension . '$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $files[] = $file[0];
        }

        return $files;
    }

    private function realpathIfPossible(string $path): string
    {
        return realpath($path) ?: $path;
    }
}
