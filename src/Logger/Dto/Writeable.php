<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Logger\Dto;

use InvalidArgumentException;

use function is_string;

final readonly class Writeable
{
    /** @phpstan-var resource */
    private mixed $resource;


    /**
     * @param string|resource $resource eg. 'php://stderr' or STDERR or 'phpunit.log'
     */
    public function __construct(mixed $resource)
    {
        if (is_string($resource)) {
            $resource = fopen($resource, 'ab');
        }

        if (!is_resource($resource)) {
            throw new InvalidArgumentException('$resource must be a resource');
        }

        $this->resource = $resource;
    }

    public function writeLine(string $string): void
    {
        fwrite($this->resource, $string . PHP_EOL);
    }
}
