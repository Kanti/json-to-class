<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Helpers;

final readonly class ExceptionHelper
{
    public static function getTypeOfClass(object $object): string
    {
        $reflection = new \ReflectionClass($object);

        if ($reflection->isTrait()) {
            return 'trait';
        }

        if ($reflection->isEnum()) {
            return 'enum';
        }

        if ($reflection->isInterface()) {
            return 'interface';
        }

        return 'class';
    }
}
