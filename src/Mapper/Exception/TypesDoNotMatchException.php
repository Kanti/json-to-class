<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper\Exception;

use Exception;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\Mapper\PossibleConvertTargets;

final class TypesDoNotMatchException extends Exception implements MapperExceptionInterface
{
    public function __construct(
        public readonly PossibleConvertTargets $possibleTypes,
        public readonly Type $sourceType,
        public readonly string $path,
        public readonly mixed $data,
    ) {
        parent::__construct('Types do not match. Possible types: ' . $possibleTypes . '. Source type: ' . $sourceType . ' at ' . $path);
    }
}
