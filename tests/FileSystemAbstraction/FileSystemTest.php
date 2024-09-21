<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\FileSystemAbstraction;

use Kanti\JsonToClass\FileSystemAbstraction\FileSystem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Safe\rmdir;
use function Safe\symlink;
use function Safe\unlink;

class FileSystemTest extends TestCase
{
    #[Test]
    public function writeContentAndRequireFile(): void
    {
        $fileName = __DIR__ . '/test.php';
        try {
            $fileSystem = new FileSystem();
            $functionName = 'f' . time();
            $content = '<?php function ' . $functionName . '() {}';
            # test writeContent
            $fileSystem->writeContent($fileName, $content);
            $this->assertEquals($content, file_get_contents($fileName), 'File should exist and have content');

            # test requireFile
            $this->assertFalse(function_exists($functionName), 'Function should not exist');
            $fileSystem->requireFile($fileName);
            $this->assertTrue(function_exists($functionName), 'Function should exist');

            $actual = $fileSystem->readContent($fileName);
            $this->assertEquals($content, $actual, 'File should exist and have content');

            $actual = $fileSystem->readContentIfExists($fileName);
            $this->assertEquals($content, $actual, 'File should exist and have content');

            unlink($fileName);
            $actual = $fileSystem->readContentIfExists($fileName);
            $this->assertNull($actual, 'File should not exist');

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/File does not exist: .*/');
            $fileSystem->readContent($fileName);
        } finally {
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }
    }

    #[Test]
    public function writeInNewDirectoryWithError(): void
    {
        try {
            $fileSystem = new FileSystem();
            $fileSystem->writeContent(__DIR__ . '/' . __FUNCTION__ . '/test.txt', __FUNCTION__);
            $this->assertDirectoryExists(__DIR__ . '/' . __FUNCTION__, 'Directory should exist');
            $this->assertFileExists(__DIR__ . '/' . __FUNCTION__ . '/test.txt', 'File should exist');
            unlink(__DIR__ . '/' . __FUNCTION__ . '/test.txt');
            rmdir(__DIR__ . '/' . __FUNCTION__);
            symlink(__FILE__, __DIR__ . '/' . __FUNCTION__);
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Directory "' . __DIR__ . '/' . __FUNCTION__ . '" was not created');
            $fileSystem->writeContent(__DIR__ . '/' . __FUNCTION__ . '/test.txt', __FUNCTION__);
        } finally {
            if (file_exists(__DIR__ . '/' . __FUNCTION__ . '/test.txt')) {
                unlink(__DIR__ . '/' . __FUNCTION__ . '/test.txt');
            }

            if (file_exists(__DIR__ . '/' . __FUNCTION__)) {
                unlink(__DIR__ . '/' . __FUNCTION__);
            }

            if (is_link(__DIR__ . '/' . __FUNCTION__)) {
                unlink(__DIR__ . '/' . __FUNCTION__);
            }

            if (is_dir(__DIR__ . '/' . __FUNCTION__)) {
                rmdir(__DIR__ . '/' . __FUNCTION__);
            }
        }
    }
}
