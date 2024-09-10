<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Abstraction;

use RuntimeException;

final class FileSystem implements FileSystemInterface
{
    public function requireFile(string $filename): void
    {
        require_once $filename;
    }

    public function readContentIfExists(string $filename): ?string
    {
        if (!file_exists($filename)) {
            return null;
        }

        $contents = file_get_contents($filename);
        assert($contents !== false, 'File exists, so content should be readable');
        return $contents;
    }

    public function writeContent(string $location, string $content): void
    {
        $directory = dirname($location);
        if (!is_dir($directory) && (!mkdir($directory, recursive: true) && !is_dir($directory))) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        file_put_contents($location, $content);
    }
}
