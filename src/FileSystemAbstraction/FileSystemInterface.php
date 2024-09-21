<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\FileSystemAbstraction;

interface FileSystemInterface
{
    public function requireFile(string $filename): void;

    public function readContent(string $filename): string;

    public function readContentIfExists(string $filename): ?string;

    public function writeContent(string $filename, string $content): void;
}
