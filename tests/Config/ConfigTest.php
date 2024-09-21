<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Config;

use Generator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\SaneConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @param array<string, string> $envs
     */
    #[Test]
    #[DataProvider('dataProvider')]
    #[BackupGlobals(true)]
    public function shouldCreateClasses(array $envs, bool $expected): void
    {
        putenv('JSON_TO_CLASS_CREATE');
        putenv('IS_DDEV_PROJECT');
        putenv('TYPO3_CONTEXT');
        putenv('APP_ENV');
        foreach ($envs as $env => $value) {
            putenv($env . '=' . $value);
        }

        $config = new SaneConfig();
        $this->assertEquals($expected, $config->shouldCreateClasses());
    }

    public static function dataProvider(): Generator
    {
        yield 'JSON_TO_CLASS_CREATE=no' => [
            'envs' => [
                'JSON_TO_CLASS_CREATE' => 'no',
            ],
            'expected' => false,
        ];
        yield 'JSON_TO_CLASS_CREATE=create' => [
            'envs' => [
                'JSON_TO_CLASS_CREATE' => 'create',
            ],
            'expected' => true,
        ];
        yield 'IS_DDEV_PROJECT=yes' => [
            'envs' => [
                'IS_DDEV_PROJECT' => 'yes',
            ],
            'expected' => true,
        ];
        yield 'nothing' => [
            'envs' => [
            ],
            'expected' => false,
        ];
        yield 'TYPO3_CONTEXT=Development/docker' => [
            'envs' => [
                'TYPO3_CONTEXT' => 'Development/docker',
            ],
            'expected' => true,
        ];
        yield 'TYPO3_CONTEXT=Development/Integration' => [
            'envs' => [
                'TYPO3_CONTEXT' => 'Development/Integration',
            ],
            'expected' => false,
        ];
        yield 'APP_ENV=dev' => [
            'envs' => [
                'APP_ENV' => 'dev',
            ],
            'expected' => true,
        ];
        yield 'APP_ENV=local' => [
            'envs' => [
                'APP_ENV' => 'local',
            ],
            'expected' => true,
        ];
        yield 'APP_ENV=production' => [
            'envs' => [
                'APP_ENV' => 'production',
            ],
            'expected' => false,
        ];
    }
}
