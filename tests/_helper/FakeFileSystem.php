<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\_helper;

use Exception;
use Throwable;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use PHPUnit\Framework\Assert;
use RuntimeException;

use function Safe\file_put_contents;
use function Safe\unlink;
use function microtime;
use function realpath;
use function sys_get_temp_dir;

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

    public function require(string $location): void
    {
        $location = realpath($location) ?: $location;
        $fileContent = $this->fileLocationsWrittenTo[$location] ?? null;
        if ($fileContent === null) {
            throw new Exception('File dose not exist: ' . $location);
        }

        $fileName = sys_get_temp_dir() . '/require' . microtime(true) . '.php';
        file_put_contents($fileName, $fileContent, FILE_APPEND);
        try {
            require $fileName;
            unlink($fileName);
        } catch (Throwable) {
            unlink($fileName);
        }
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

        Assert::assertEquals($expected, $this->fileLocationsWrittenTo);
    }
}
