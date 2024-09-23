<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Helpers;

use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;

final class StringHelpers
{
    public static function getAttributes(Attribute $attribute): object
    {
        $className = $attribute->getName();
        return new $className(...array_map(self::unpackLiterals(...), $attribute->getArguments()));
    }

    /**
     * @param list< list<mixed>|Literal|string >|Literal|string $argument
     * @return string|list< string|list<mixed> >
     */
    private static function unpackLiterals(Literal|array|string $argument): string|array
    {
        if (is_array($argument)) {
            return array_map(self::unpackLiterals(...), $argument);
        }

        if ($argument instanceof Literal) {
            $argument = eval('return ' . $argument . ';');
            assert(is_string($argument));
        }

        return $argument;
    }

    public static function getChildClass(string $className, string $property): string
    {

        $className2 = ucfirst($property);
        if (Helpers::Keywords[strtolower($property)] ?? false) {
            $className2 = '_' . $className2;
        }

        return $className . '\\' . $className2;
    }
}
