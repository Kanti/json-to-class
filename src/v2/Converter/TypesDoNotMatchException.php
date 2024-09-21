<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Converter;

use Exception;
use Kanti\JsonToClass\v2\Dto\Type;

final class TypesDoNotMatchException extends Exception
{
    public function __construct(public readonly PossibleConvertTargets $possibleTypes, public readonly Type $sourceType)
    {
        parent::__construct('Types do not match. Possible types: ' . $possibleTypes . '. Source type: ' . $sourceType);
    }
}
