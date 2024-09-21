<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\_helper;

final class PhpFilesDto
{
    /**
     * @param array<string, string> $phpClasses
     * @param array<mixed> $providedTestData
     */
    public function __construct(
        public array $phpClasses,
        public int|string $dataName,
        public array $providedTestData,
    ) {
    }
}
