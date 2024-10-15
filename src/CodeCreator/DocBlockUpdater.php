<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use InvalidArgumentException;

use function str_contains;
use function trim;

final readonly class DocBlockUpdater
{
    public function updateVarBlock(?string $comment, string $phpType, ?string $docBlockType): ?string
    {
        $docBlockType = $docBlockType ?: null;
        $hasComment = $comment !== null && trim(preg_replace('/@var\s+[^\s]+/m', '', $comment) ?? throw new InvalidArgumentException('regex failed'));

        if (!$hasComment) {
            if ($docBlockType) {
                return "@var " . $docBlockType;
            }

            return null;
        }

        if (!str_contains($comment, '@var')) {
            $comment = '@var mixed ' . ltrim($comment);
        }

        $docBlockType ??= $phpType;
        return preg_replace('/(@var\s+.+)\s+(.+)/mU', '@var ' . $docBlockType . ' $2', $comment) ?? throw new InvalidArgumentException('regex failed');
    }
}
