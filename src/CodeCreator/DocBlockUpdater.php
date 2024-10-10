<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use InvalidArgumentException;

use function Safe\preg_match;
use function Safe\preg_replace;

use const PHP_EOL;

final readonly class DocBlockUpdater
{
    /**
     * if $docBlockType is null, there should not be a docblock for this parameter (but if it has a comment, it should stay)
     * if $phpType is null, it should be removed from the docblock even if it has a comment
     */
    public function updateDocblock(string $comment, string $name, ?string $phpType, ?string $docBlockType): ?string
    {
        $paramExists = $phpType !== null;
        $documentationShouldExist = $docBlockType !== null;
        $paramIsDocumented = preg_match('/^([^\S\r\n]*@param[^\S\r\n]+.*[^\S\r\n]+\$' . $name . ')([^\S\r\n]+[^\r\n]*)?$/m', $comment);
        $paramHasComment = preg_match('/^([^\S\r\n]*@param[^\S\r\n]+.*[^\S\r\n]+\$' . $name . '[^\S\r\n]+[^\r\n]+)$/m', $comment);
        if (!$paramExists && $documentationShouldExist) {
            throw new InvalidArgumentException('If $docBlockType is set, $phpType must also be set');
        }

        $newPart = "@param " . $docBlockType . " $" . $name;
        if (!$paramIsDocumented) {
            if (!$paramExists) {
                return null;
            }

            if ($documentationShouldExist) {
                return $comment . ($comment ? PHP_EOL . $newPart : $newPart);
            }

            return null;
        }

        if ($documentationShouldExist) {
            return preg_replace('/^([^\S\r\n]*@param[^\S\r\n]+.*[^\S\r\n]+\$' . $name . ')([^\S\r\n]+[^\r\n]*)?$/m', $newPart . '$2', $comment);
        }

        if (!$paramHasComment || !$paramExists) {
            $comment = preg_replace('/^([^\S\r\n]*@param[^\S\r\n]+.*[^\S\r\n]+\$' . $name . ')([^\S\r\n]+[^\r\n]*)?$/m', 'REPLACE_ME_TO_REMOVE_NEWLINES', $comment);
            $comment = preg_replace('/\nREPLACE_ME_TO_REMOVE_NEWLINES\n/', PHP_EOL, $comment);
            return preg_replace('/\n?REPLACE_ME_TO_REMOVE_NEWLINES\n?/', '', $comment);
        }

        $newPart = "@param " . $phpType . " $" . $name;
        return preg_replace('/^([^\S\r\n]*@param[^\S\r\n]+.*[^\S\r\n]+\$' . $name . ')([^\S\r\n]+[^\r\n]*)?$/m', $newPart . '$2', $comment);
    }
}
