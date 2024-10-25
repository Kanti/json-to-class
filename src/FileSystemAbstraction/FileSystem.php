<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\FileSystemAbstraction;

use RuntimeException;

final readonly class FileSystem implements FileSystemInterface
{
    public function writeContent(string $filename, string $content): void
    {
        $filename = $this->realpathIfPossible($filename);
        $directory = dirname($filename);
        if (!is_dir($directory) && (!@mkdir($directory, recursive: true) && !is_dir($directory))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        file_put_contents($filename, $content);
    }

    public function readContent(string $filename): string
    {
        $filename = $this->realpathIfPossible($filename);
        $result = $this->readContentIfExists($filename);
        if ($result === null) {
            throw new RuntimeException('File does not exist: ' . $filename);
        }

        return $result;
    }

    public function readContentIfExists(string $filename): ?string
    {
        $filename = $this->realpathIfPossible($filename);
        if (!file_exists($filename)) {
            return null;
        }

        $contents = file_get_contents($filename);
        assert($contents !== false, 'File exists, so content should be readable');
        return $contents;
    }

    public function require(string $location): mixed
    {
        return require $location;
    }

    private function realpathIfPossible(string $path): string
    {
        return realpath($path) ?: $path;
    }
}
