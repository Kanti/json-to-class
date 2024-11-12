<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Logger;

use Kanti\JsonToClass\Logger\Dto\Writeable;
use Psr\Clock\ClockInterface;
use Psr\Log\AbstractLogger;
use Stringable;

use function json_encode;

final class StdErrLogger extends AbstractLogger
{
    public function __construct(private readonly ClockInterface $clock, private readonly Writeable $writeable)
    {
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $data = [
            'level' => $level,
            'time' => $this->clock->now()->format('c'),
            'message' => $message,
            ...$context,
        ];
        $string = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->writeable->writeLine($string);
    }
}
