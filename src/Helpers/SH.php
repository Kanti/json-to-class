<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Helpers;

use Nette\PhpGenerator\Attribute;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;

final class SH
{
    public static function getAttributes(Attribute $attribute): object
    {
        $className = $attribute->getName();
        return new $className(...self::unpackLiterals($attribute->getArguments()));
    }

    /**
     * @param Literal|string|list<Literal|string>|list<list<Literal|string>> $argument
     * @return string|list<string>|list<list<string>>
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

    /**
     * @param class-string $className
     * @return class-string
     */
    public static function getChildClass(string $className, string $property): string
    {
        $className2 = ucfirst($property);
        if (Helpers::Keywords[strtolower($property)] ?? false) {
            $className2 = '_' . $className2;
        }

        return self::classString($className . '\\' . $className2);
    }

    /**
     * phpstan helper so you can elevate a string to a class-string
     *
     * @return class-string
     */
    public static function classString(string $name): string
    {
        /** @var class-string $name */
        return $name;
    }
}
