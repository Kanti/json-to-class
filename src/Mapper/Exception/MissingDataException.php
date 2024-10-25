<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper\Exception;

use Exception;
use Kanti\JsonToClass\Mapper\PossibleConvertTargets;

final class MissingDataException extends Exception implements MapperExceptionInterface
{
    public function __construct(
        public readonly PossibleConvertTargets $possibleTypes,
        public readonly string $path,
    ) {
        parent::__construct('Missing data. Possible types: ' . $possibleTypes . ' at ' . $path);
    }
}
