<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\CodeCreator;

use Generator;
use Kanti\JsonToClass\CodeCreator\DocBlockUpdater;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

use function assert;

class DocBlockUpdaterTest extends TestCase
{
    #[Test]
    #[TestDox('If $docBlockType is set, $phpType must also be set')]
    public function exception1(): void
    {
        $container = new JsonToClassContainer();
        $updater = $container->get(DocBlockUpdater::class);

        $this->expectExceptionMessage('If $docBlockType is set, $phpType must also be set');
        $updater->updateDocblock('', 'name', null, 'array');
    }

    #[Test]
    #[DataProvider('updateDocblockDataProvider')]
    public function updateDocblock(string $input, string $name, ?string $phpType = null, ?string $docBlockType = null, ?string $expected = null): void
    {
        $container = new JsonToClassContainer();
        $updater = $container->get(DocBlockUpdater::class);
        $phpType ??= $docBlockType;

        $actual = $updater->updateDocblock($input, $name, $phpType, $docBlockType);
        $this->assertEquals($expected, $actual);
    }

    public static function updateDocblockDataProvider(): Generator
    {
        yield '!paramExists !documentationShouldExist !paramIsDocumented !paramHasComment' => [
            'input' => '',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => null,
        ];
        yield 'paramExists !documentationShouldExist !paramIsDocumented !paramHasComment' => [
            'input' => '',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => null,
        ];
        yield 'paramExists documentationShouldExist !paramIsDocumented !paramHasComment' => [
            'input' => '',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => 'string',
            'expected' => '@param string $name',
        ];


        yield '!paramExists !documentationShouldExist paramIsDocumented !paramHasComment' => [
            'input' => '@param string $name',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'paramExists !documentationShouldExist paramIsDocumented !paramHasComment' => [
            'input' => '@param string $name',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'paramExists documentationShouldExist paramIsDocumented !paramHasComment' => [
            'input' => '@param string $name ', // space at the end is not comment
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => 'string',
            'expected' => '@param string $name ',
        ];

        yield '!paramExists !documentationShouldExist paramIsDocumented paramHasComment' => [
            'input' => '@param string $name this is a comment',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'paramExists !documentationShouldExist paramIsDocumented paramHasComment' => [
            'input' => '@param string $name this is a comment',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '@param string $name this is a comment',
        ];
        yield 'paramExists documentationShouldExist paramIsDocumented paramHasComment' => [
            'input' => '@param string $name this is a comment',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => 'string',
            'expected' => '@param string $name this is a comment',
        ];


        yield 'empty' => [
            'input' => '',
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => '@param string $name',
        ];
        yield 'missing' => [
            'input' => '@param Name2 $name2',
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => <<<'EOF'
                              @param Name2 $name2
                              @param string $name
                              EOF
            ,
        ];
        yield 'has param with same type' => [
            'input' => '@param string $name',
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => '@param string $name',
        ];
        yield 'has param wrong same type' => [
            'input' => <<<'EOF'
                           @param null $name
                           @param null $name2
                           EOF
            ,
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => <<<'EOF'
                              @param string $name
                              @param null $name2
                              EOF
            ,
        ];
        yield 'has docBlockType but should not' => [
            'input' => '@param string $name',
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'has phpType but should not' => [
            'input' => '@param string $name',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'has param with same type + comment' => [
            'input' => '@param string $name this is a comment',
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => '@param string $name this is a comment',
        ];
        yield 'has param wrong same type + comment' => [
            'input' => '@param null $name this is a comment',
            'name' => 'name',
            'docBlockType' => 'string',
            'expected' => '@param string $name this is a comment',
        ];
        yield 'has docBlockType but should not + comment' => [
            'input' => <<<'EOF'
                           @param Name2 $name2
                           @param WrongType $name this is a comment
                           EOF
            ,
            'name' => 'name',
            'phpType' => 'string',
            'docBlockType' => null,
            'expected' => <<<'EOF'
                              @param Name2 $name2
                              @param string $name this is a comment
                              EOF
            ,
        ];
        yield 'has phpType but should not + comment' => [
            'input' => '@param string $name this is a comment',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '',
        ];
        yield 'should not change other params' => [
            'input' => '@param string $x this is a comment',
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => null,
        ];
        yield 'should not change other params but remove the correct one' => [
            'input' => <<<'EOF'
                           @param string $x this is a comment
                           @param string $name this is a comment
                           EOF
            ,
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '@param string $x this is a comment',
        ];
        yield 'should not change other params but remove the correct one diffrent Sorting' => [
            'input' => <<<'EOF'
                           @param string $name this is a comment
                           @param string $x this is a comment
                           EOF
            ,
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => '@param string $x this is a comment',
        ];
        yield 'should not change other params but remove the correct one before and after' => [
            'input' => <<<'EOF'
@param string $name2
@param string $name
@param string $x this is a comment
EOF
            ,
            'name' => 'name',
            'phpType' => null,
            'docBlockType' => null,
            'expected' => <<<'EOF'
@param string $name2
@param string $x this is a comment
EOF,
        ];
    }
}
