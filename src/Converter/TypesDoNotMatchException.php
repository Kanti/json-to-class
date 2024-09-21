<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Converter;

use Exception;
use Kanti\JsonToClass\Dto\Type;

final class TypesDoNotMatchException extends Exception
{
    public function __construct(public readonly PossibleConvertTargets $possibleTypes, public readonly Type $sourceType, public readonly string $path)
    {
        parent::__construct('Types do not match. Possible types: ' . $possibleTypes . '. Source type: ' . $sourceType . ' at ' . $path);
    }
}
