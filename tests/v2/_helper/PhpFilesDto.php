<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\v2\_helper;

use Kanti\JsonToClass\v2\Schema\Schema;

final class PhpFilesDto
{
    /**
     * @param array<string, string> $phpCode
     */
    public function __construct(
        public array $phpCode,
        public int|string $dataName,
        public array $providedData,
    ) {}
}
