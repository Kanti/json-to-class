<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Mapper\Exception;

use Exception;
use Kanti\JsonToClass\Dto\Type;

final class NoPossibleTypesException extends Exception implements MapperExceptionInterface
{
    public function __construct(
        public readonly Type $sourceType,
        public readonly string $path,
        public readonly mixed $data,
    ) {
        parent::__construct('No possible types. Source type: ' . $sourceType . ' at ' . $path);
    }
}
