<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Logger;

use RuntimeException;
use DateTimeZone;
use DateTimeImmutable;
use Kanti\JsonToClass\Logger\Dto\Writeable;
use Kanti\JsonToClass\Logger\StdErrLogger;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

use function fopen;

class StdErrLoggerTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    public function logFormat(): void
    {
        $resource = fopen('php://memory', 'rwb+') ?: throw new RuntimeException('Failed to open memory stream');
        $logger = new StdErrLogger(
            new class implements ClockInterface {
                public function now(): DateTimeImmutable
                {
                    return new DateTimeImmutable('2021-10-14', new DateTimeZone('UTC'));
                }
            },
            new Writeable($resource),
        );
        $logger->log('error', 'message', ['context' => 'value']);

        $expected = '{"level":"error","time":"2021-10-14T00:00:00+00:00","message":"message","context":"value"}' . PHP_EOL;
        rewind($resource);
        $this->assertEquals($expected, stream_get_contents($resource));
    }
}
