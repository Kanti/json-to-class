<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Abstraction;

use Kanti\JsonToClass\Tests\Code\FileWriterTest;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class FakeFileSystem implements FileSystemInterface
{
    /**
     * @param list<string> $alreadyWrittenFiles
     * @param list<string> $fileLocationsWrittenTo
     */
    public function __construct(public array $alreadyWrittenFiles, public array $fileLocationsWrittenTo)
    {
    }

    public function requireFile(string $filename): void
    {
        // noop
    }

    public function writeContent(string $location, string $content): void
    {
        assertArrayHasKey($location, array_flip(array_values($this->fileLocationsWrittenTo)), 'Is file written to');
        assertEquals(FileWriterTest::CONTENT, $content, 'is content equal');
    }

    public function readContentIfExists(string $filename): ?string
    {
        return in_array($filename, $this->alreadyWrittenFiles, true) ? FileWriterTest::CONTENT : null;
    }
}
