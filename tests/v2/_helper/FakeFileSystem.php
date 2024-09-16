<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\_helper;

use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class FakeFileSystem implements FileSystemInterface
{
    public const CONTENT = 'Content';

    /**
     * @param list<string> $alreadyWrittenFiles
     * @param list<string> $fileLocationsWrittenTo
     */
    public function __construct(public array $alreadyWrittenFiles, public array $fileLocationsWrittenTo)
    {
        if (!array_is_list($alreadyWrittenFiles)) {
            throw new \InvalidArgumentException('alreadyWrittenFiles must be a list');
        }
        if (!array_is_list($fileLocationsWrittenTo)) {
            throw new \InvalidArgumentException('fileLocationsWrittenTo must be a list');
        }
    }

    public function requireFile(string $filename): void
    {
        // noop
    }

    public function writeContent(string $filename, string $content): void
    {
        $filename = realpath($filename) ?: $filename;
        assertArrayHasKey(
            $filename,
            array_flip(array_values($this->fileLocationsWrittenTo)),
            '?? This file should not be written to one of these locations are allowed: ' . implode(', ', $this->fileLocationsWrittenTo),
        );
        assertEquals(self::CONTENT, $content, 'is content equal');
    }

    public function readContent(string $filename): string
    {
        $result = $this->readContentIfExists($filename);
        if ($result === null) {
            throw new \RuntimeException('File does not exist: ' . $filename);
        }
        return $result;
    }

    public function readContentIfExists(string $filename): ?string
    {
        $filename = realpath($filename) ?: $filename;
        if (in_array($filename, $this->alreadyWrittenFiles, true)) {
            return self::CONTENT;
        }
        return null;
    }

    public function listFiles(string $directory, string $extension, bool $recursive = true): array
    {
        throw new \RuntimeException('Not implemented');
    }
}
