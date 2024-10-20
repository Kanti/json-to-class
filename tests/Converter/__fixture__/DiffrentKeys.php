<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

use Kanti\JsonToClass\Attribute\Key;

final class DiffrentKeys
{
    #[Key('name✨')]
    public string $name;

    #[Key('age✨')]
    public int $age;

    public static function from(string $name, int $age): self
    {
        $self = new self();
        $self->name = $name;
        $self->age = $age;
        return $self;
    }
}
