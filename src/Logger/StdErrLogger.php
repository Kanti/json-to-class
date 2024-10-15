<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Logger;

use Psr\Log\AbstractLogger;
use Stringable;

use function Safe\fopen;
use function fwrite;
use function json_encode;

use const PHP_EOL;

final class StdErrLogger extends AbstractLogger
{
    /** @var ?resource */
    private mixed $resource = null;

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $data = [
            'level' => $level,
            'time' => date('c'),
            'message' => $message,
            ...$context,
        ];
        $string = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $phpUnit = defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__');
        $this->resource ??= $phpUnit ? fopen('phpunit.log', 'ab') : STDERR;
        fwrite($this->resource, $string . PHP_EOL);
    }
}
