<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Helpers;

use Exception;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Closure;
use Nette\PhpGenerator\Constant;
use Nette\PhpGenerator\EnumCase;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\Property;
use Nette\PhpGenerator\TraitType;
use RuntimeException;

use function array_filter;
use function array_values;
use function get_debug_type;
use function sprintf;

enum F
{
    /**
     * @template T of object
     * @param class-string<T> $classString
     * @return T|null
     */
    public static function getAttribute(
        string $classString,
        ClassLike|Closure|Constant|EnumCase|GlobalFunction|Method|Parameter|Property $attributeAware,
    ): ?object {
        $attributes = array_values(array_filter($attributeAware->getAttributes(), fn($attribute): bool => $attribute->getName() === $classString));

        if (!$attributes) {
            return null;
        }

        if (count($attributes) > 1) {
            throw new Exception(sprintf('Multiple attributes found %s', $classString));
        }

        return new $classString(...self::unpackLiterals(array_values($attributes[0]->getArguments())));
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

    /**
     * @phpstan-assert ClassType $class
     * @param class-string $className
     */
    public static function assertClassType(mixed $class, string $className): void
    {
        if ($class instanceof ClassType) {
            return;
        }

        throw new RuntimeException(sprintf("Expected ClassType, got %s for class %s", get_debug_type($class), $className));
    }
}
