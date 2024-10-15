<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

final class Children
{
    public string $name;

    public int $age;

    public static function from(string $name, int $age): self
    {
        $self = new self();
        $self->name = $name;
        $self->age = $age;
        return $self;
    }
}
