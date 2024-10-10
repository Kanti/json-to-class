<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\_helper;

use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use RuntimeException;

use function PHPUnit\Framework\assertEquals;

final class FakeFileSystem implements FileSystemInterface
{
    public const CONTENT = 'Content';

    /** @var array<string, string>  */
    private array $fileState;

    /** @var array<string, string>  */
    private array $fileLocationsWrittenTo = [];

    /**
     * @param array<string, string|true> $alreadyWrittenFiles
     */
    public function __construct(array $alreadyWrittenFiles = [])
    {
        $a = [];
        foreach ($alreadyWrittenFiles as $filename => $content) {
            $a[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }

        $this->fileState = $a;
    }

    public function requireFile(string $filename): void
    {
        // noop
    }

    public function writeContent(string $filename, string $content): void
    {
        $filename = realpath($filename) ?: $filename;
        if (isset($this->fileLocationsWrittenTo[$filename])) {
            throw new RuntimeException('File already writtenTo: ' . $filename);
        }

        $this->fileState[$filename] = $content;
        $this->fileLocationsWrittenTo[$filename] = $content;
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
        return $this->fileState[$filename] ?? null;
    }

    /**
     * @param array<string, string|true> $expectedFiles
     */
    public function assertFilesWrittenTo(array $expectedFiles): void
    {

        $expected = [];
        foreach ($expectedFiles as $filename => $content) {
            $expected[realpath($filename) ?: $filename] = $content === true ? self::CONTENT : $content;
        }

        assertEquals($expected, $this->fileLocationsWrittenTo);
    }
}
