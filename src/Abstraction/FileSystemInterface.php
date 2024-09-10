<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Abstraction;

interface FileSystemInterface
{
    public function requireFile(string $filename): void;

    public function readContentIfExists(string $filename): ?string;

    public function writeContent(string $location, string $content): void;
}
