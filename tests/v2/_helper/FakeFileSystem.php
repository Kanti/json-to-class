<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\_helper;

use RuntimeException;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use PHPUnit\Framework\ExpectationFailedException;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class FakeFileSystem implements FileSystemInterface
{
    public const CONTENT = 'Content';

    private array $alreadyWrittenFiles = [];

    private array $fileLocationsWrittenTo = [];

    /**
     * @param array<string, string|true> $alreadyWrittenFiles
     * @param array<string, string|true> $fileLocationsWrittenTo
     */
    public function __construct(array $alreadyWrittenFiles = [], array $fileLocationsWrittenTo = [])
    {
        foreach ($alreadyWrittenFiles as $filename => $content) {
            $this->alreadyWrittenFiles[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }

        foreach ($fileLocationsWrittenTo as $filename => $content) {
            $this->fileLocationsWrittenTo[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }
    }

    public function requireFile(string $filename): void
    {
        // noop
    }

    public function writeContent(string $filename, string $content): void
    {
        $filename = realpath($filename) ?: $filename;
        assertArrayHasKey($filename, $this->fileLocationsWrittenTo, 'File should not be written to: ' . $filename . ' allowed locations: ' . implode(', ', array_keys($this->fileLocationsWrittenTo)));
        $expectedContent = $this->fileLocationsWrittenTo[$filename];
        assertEquals($expectedContent, $content, 'is content equal');
    }

    public function readContent(string $filename): string
    {
        $result = $this->readContentIfExists($filename);
        if ($result === null) {
            throw new RuntimeException('File does not exist: ' . $filename);
        }

        return $result;
    }

    public function readContentIfExists(string $filename): ?string
    {
        $filename = realpath($filename) ?: $filename;
        return $this->alreadyWrittenFiles[$filename] ?? null;
    }
}
