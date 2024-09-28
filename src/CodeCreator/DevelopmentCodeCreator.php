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

        $result = [];
        foreach ($schema->properties ?? [] as $name => $property) {
            $this->createDevelopmentClasses($property);

            $types = $this->typeCreator->getAttributeTypes($property, null);
            $result[] = new Parameter($name, (new Types(...$types))->types, $schema->canBeMissing);
        }

        $classNameImplementation = $this->createDevelopmentClassIfNotExists($schema->className);
        $classNameImplementation::$__kanti_json_to_class_parameters = $result;
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
        return $implementation;
    }
}
