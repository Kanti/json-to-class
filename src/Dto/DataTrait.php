<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

trait DataTrait
{
    public function __construct(mixed ...$data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
