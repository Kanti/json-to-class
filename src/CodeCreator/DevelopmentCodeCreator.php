<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\DataTrait;
use Kanti\JsonToClass\Dto\Parameter;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\Helpers;

final readonly class DevelopmentCodeCreator
{
    public function __construct(private TypeCreator $typeCreator)
    {
    }

    public function createDevelopmentClasses(NamedSchema $schema): void
    {
        if ($schema->listElement) {
            $this->createDevelopmentClasses($schema->listElement);
        }

        $parameters = [];
        foreach ($schema->properties ?? [] as $name => $property) {
            $this->createDevelopmentClasses($property);

            $types = $this->typeCreator->getAttributeTypes($property, null);
            $parameters[] = new Parameter($name, (new Types(...$types))->types, $property->canBeMissing);
        }

        $classNameImplementation = $this->createDevelopmentClassIfNotExists($schema->className);
        $classNameImplementation::setClassParameters(...$parameters);
    }

    /**
     * @return class-string<DataTrait>
     */
    private function createDevelopmentClassIfNotExists(string $className): string
    {
        if (class_exists($className, false)) {
            throw new Exception(sprintf("Class %s already exists %b", $className, interface_exists($className)));
        }

        $implementation = $className . '_Implementation';

        if (interface_exists($className, false) && class_exists($implementation, false)) {
            if (!self::isDevelopmentDto($implementation)) {
                throw new Exception(sprintf("Class %s already exists but is not a DataTrait %b", $implementation, interface_exists($implementation)));
            }

            return $implementation;
        }

        $shortName = Helpers::extractShortName($className);
        $namespace = Helpers::extractNamespace($className);
        eval(<<<PHP
namespace {$namespace} {
    interface {$shortName} {}
    class_alias(
        get_class(new class implements {$shortName} {
            use \Kanti\JsonToClass\Dto\DataTrait;
        }),
        {$shortName}_Implementation::class
    );
}
PHP
        );
        if (!class_exists($implementation, false) || !self::isDevelopmentDto($implementation)) {
            throw new Exception(sprintf("Class %s exists but is not a DataTrait %b", $implementation, interface_exists($implementation)));
        }

        return $implementation;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @phpstan-assert-if-true class-string<DataTrait> $className
     */
    public static function isDevelopmentDto(string $className): bool
    {
        return isset(class_uses($className, false)[DataTrait::class]);
    }
}
