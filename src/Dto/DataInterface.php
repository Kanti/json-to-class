<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

interface DataInterface
{
    /**
     * @return list<Parameter>
     */
    public static function getClassParameters(): array;

    public static function setClassParameters(Parameter ...$parameters): void;
}
