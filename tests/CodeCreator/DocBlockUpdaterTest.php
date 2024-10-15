<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use InvalidArgumentException;
use Generator;
use Kanti\JsonToClass\CodeCreator\DocBlockUpdater;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function assert;

use const PHP_EOL;

class DocBlockUpdaterTest extends TestCase
{
    #[Test]
    #[DataProvider('dataProvider')]
    public function updateVarBlock(?string $input, ?string $phpType, ?string $docBlockType, ?string $expected, mixed ...$_): void
    {
        $container = new JsonToClassContainer();
        $updater = $container->get(DocBlockUpdater::class);
        assert($updater instanceof DocBlockUpdater);

        $phpType ??= $docBlockType ?? throw new InvalidArgumentException('phpType or docBlockType must be set');

        $actual = $updater->updateVarBlock($input, $phpType, $docBlockType);
        $this->assertEquals($expected, $actual);
    }

    public static function dataProvider(): Generator
    {
        yield 'nothing' => [
            'input' => null,
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => null,
        ];
        yield 'docBlockType set' => [
            'input' => null,
            'phpType' => 'string',
            'docBlockType' => 'string',
            'expected' => '@var string',
        ];
        yield 'comment present' => [
            'input' => '@var mixed comment',
            'phpType' => 'string',
            'docBlockType' => 'string',
            'expected' => '@var string comment',
        ];
        yield 'comment present but should not have docblock' => [
            'input' => '@var mixed comment',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '@var string comment',
        ];
        yield 'comment without @var' => [
            'input' => 'comment',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '@var string comment',
        ];
        yield 'multiline comment without @var' => [
            'input' => 'comment' . PHP_EOL . 'Help',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '@var string comment' . PHP_EOL . 'Help',
        ];
        yield '@var between comments' => [
            'input' => 'comment' . PHP_EOL . '@var mixed comment' . PHP_EOL . 'Help',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => 'comment' . PHP_EOL . '@var string comment' . PHP_EOL . 'Help',
        ];
        yield '@var string this is not a UUid' => [
            'input' => '@var string this is not a UUid',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '@var string this is not a UUid',
        ];
    }
}
