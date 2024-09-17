<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\_helper;

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

use function Safe\file_put_contents;
use function Safe\shell_exec;
use function Safe\unlink;

final class PhpFilesDriver implements Driver
{
    public function extension(): string
    {
        return 'md';
    }

    public function match(mixed $expected, mixed $actual): void
    {
        Assert::assertEquals($expected, $this->serialize($actual, false));

        foreach ($actual->phpCode as $className => $value) {
            $this->lintPhpFile($value, $className);
        }
    }

    public function serialize(mixed $data, bool $lint = true): string
    {
        if (!$data instanceof PhpFilesDto) {
            throw new CantBeSerialized('Resources can not be serialized to json');
        }

        $result = '';
        $result .= '# Tested "' . $data->dataName . '"' . PHP_EOL;
        $result .= '````json' . PHP_EOL;

        $result .= json_encode($data->providedData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS) . PHP_EOL;

        $result .= '````' . PHP_EOL;

        foreach ($data->phpClasses as $className => $value) {
            Assert::assertIsString($className);
            Assert::assertIsString($value);

            if ($lint) {
                $this->lintPhpFile($value, $className);
            }

            $result .= '##### ' . $className . ':' . PHP_EOL;
            $result .= '````php' . PHP_EOL;
            Assert::assertStringEndsWith(PHP_EOL, $value);
            $result .= $value;
            $result .= '````' . PHP_EOL;
        }

        return $result;
    }

    private function lintPhpFile(string $value, string $className): void
    {
        if (!str_starts_with($value, '<?php')) {
            throw new CantBeSerialized('Php code must start with "<?php"');
        }

        try {
            $filename = 'test' . time() . '.php';
            file_put_contents($filename, $value);
            $output = shell_exec('php -l ' . $filename . ' 2>&1');
            Assert::assertStringContainsString('No syntax errors detected', $output, '?? Invalid php code detected for class: ' . $className . PHP_EOL . $value);
        } finally {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }
}
