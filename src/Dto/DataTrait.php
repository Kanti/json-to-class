<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

trait DataTrait
{
    /** @var list<Parameter> */
    private static array $__classParameters = [];

    public function __construct(mixed ...$data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @return list<Parameter>
     */
    public static function getClassParameters(): array
    {
        return static::$__classParameters;
    }

    /**
     * @param list<Parameter> $classParameters
     */
    public static function setClassParameters(Parameter ...$parameters): void
    {
        static::$__classParameters = array_values($parameters);
    }
}
