<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\FileSystemAbstraction;

interface FileSystemInterface
{
    public function requireFile(string $filename): void;

    public function readContent(string $filename): string;

    public function readContentIfExists(string $filename): ?string;

    public function writeContent(string $location, string $content): void;

    /**
     * @param string $directory
     * @param string $extension
     * @param bool $recursive
     * @return list<string>
     */
    public function listFiles(string $directory, string $extension, bool $recursive = true): array;
}
