<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\_helper;

use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use RuntimeException;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final readonly class FakeFileSystem implements FileSystemInterface
{
    public const CONTENT = 'Content';

    /** @var array<string, string>  */
    private array $alreadyWrittenFiles;

    /** @var array<string, string>  */
    private array $fileLocationsWrittenTo;

    /**
     * @param array<string, string|true> $alreadyWrittenFiles
     * @param array<string, string|true> $fileLocationsWrittenTo
     */
    public function __construct(array $alreadyWrittenFiles = [], array $fileLocationsWrittenTo = [])
    {
        $a = [];
        foreach ($alreadyWrittenFiles as $filename => $content) {
            $a[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }

        $this->alreadyWrittenFiles = $a;

        $f = [];
        foreach ($fileLocationsWrittenTo as $filename => $content) {
            $f[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }

        $this->fileLocationsWrittenTo = $f;
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
