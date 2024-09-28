<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

trait DataTrait
{
    /** @var list<Parameter> */
    public static array $__kanti_json_to_class_parameters = [];

    public function __construct(private array $data = [])
    {
    }

    public function __has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}
