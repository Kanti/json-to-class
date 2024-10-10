<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Validator;

use Generator;
use InvalidArgumentException;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Config\StrictConfig;
use Kanti\JsonToClass\Validator\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValidatorTest extends TestCase
{
    /**
     * @param bool|int|float|string|array<mixed>|stdClass|null $data
     */
    #[Test]
    #[DataProvider('dataProvider')]
    public function validateData(null|bool|int|float|string|array|stdClass $data, Config $config = new SaneConfig(), ?string $invalidMessage = null): void
    {
        $validator = new Validator();
        if ($invalidMessage) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage($invalidMessage);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $validator->validateData($data, $config);
    }

    public static function dataProvider(): Generator
    {
        yield 'null' => [
            'data' => null,
        ];
        yield 'bool' => [
            'data' => true,
        ];
        yield 'int' => [
            'data' => 1,
        ];
        yield 'float' => [
            'data' => 1.1,
        ];
        yield 'string' => [
            'data' => 'string',
        ];
        yield 'empty array' => [
            'data' => [],
        ];
        yield 'array with true' => [
            'data' => [true],
        ];
        yield 'array with true + StrictConfig' => [
            'data' => [true],
            'config' => new StrictConfig(),
        ];
        yield 'empty record' => [
            'data' => new stdClass(),
        ];
        yield 'record with valid Key' => [
            'data' => ['validKey' => 'value'],
        ];
        yield 'record with reserved Key' => [
            'data' => ['int' => 'value'],
        ];
        yield 'record with number' => [
            'data' => [42 => 'value'],
        ];
        yield 'record with number + StrictConfig' => [
            'data' => [42 => 'value'],
            'config' => new StrictConfig(),
            'invalidMessage' => 'Key is not valid: 42',
        ];
        yield 'record with numberStartingKey' => [
            'data' => ['0111' => 'value'],
        ];
        yield 'record with numberStartingKey + StrictConfig' => [
            'data' => ['0111' => 'value'],
            'config' => new StrictConfig(),
            'invalidMessage' => 'Key is not valid: 0111',
        ];
        yield 'record with invalid Key' => [
            'data' => ['*~+' => 'value'],
            'invalidMessage' => 'Key is not valid: *~+',
        ];
        yield 'inList record with numberStartingKey + StrictConfig' => [
            'data' => [['0111' => 'value']],
            'config' => new StrictConfig(),
            'invalidMessage' => 'Key is not valid: 0111 at $[0]',
        ];
        yield 'inList record with invalid Key' => [
            'data' => [['*~+' => 'value']],
            'invalidMessage' => 'Key is not valid: *~+ at $[0]',
        ];
        yield 'inObject record with numberStartingKey + StrictConfig' => [
            'data' => ['parentKey' => ['0111' => 'value']],
            'config' => new StrictConfig(),
            'invalidMessage' => 'Key is not valid: 0111 at $.parentKey',
        ];
        yield 'inObject record with invalid Key' => [
            'data' => ['parentKey' => ['*~+' => 'value']],
            'invalidMessage' => 'Key is not valid: *~+ at $.parentKey',
        ];
    }
}
