<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use Exception;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\Helpers\SH;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PromotedParameter;

final readonly class SchemaFromClassCreator
{
    public function __construct(
        private ClassLocator $classLocator,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function fromClasses(string $className): ?NamedSchema
    {
        $class = $this->classLocator->getClass($className);
        if (!$class) {
            return null;
        }

        $schema = new NamedSchema($className);
        $this->loopSchema($schema, $class);
        return $schema;
    }

    public function loopSchema(NamedSchema $schema, ClassType $class): void
    {
        $schema->properties ??= [];

        foreach ($this->getPromotedParameters($class, $schema->className) as $parameter) {
            $propertyName = $parameter->getName();
            $types = $this->getTypesFromPhpProperty($parameter, $schema->className);
            $childClassName = SH::getChildClass($schema->className, $propertyName);
            foreach ($types as $type) {
                $schema->properties[$propertyName] ??= new NamedSchema($childClassName);

                $this->addType($type, $schema->properties[$propertyName]);

                if ($parameter->hasDefaultValue()) {
                    $schema->properties[$propertyName]->canBeMissing = true;
                }
            }
        }
    }

    /**
     * @param class-string $className
     * @return list<PromotedParameter>
     */
    private function getPromotedParameters(ClassType $class, string $className): array
    {
        $result = [];
        foreach ($class->getMethod('__construct')->getParameters() as $parameter) {
            if (!$parameter instanceof PromotedParameter) {
                throw new Exception('Parameter is not a PromotedParameter ' . $className . '->' . $parameter->getName());
            }

            $result[] = $parameter;
        }

        return $result;
    }

    /**
     * @param class-string $className
     * @return list<Type>
     */
    private function getTypesFromPhpProperty(PromotedParameter $parameter, string $className): array
    {
        try {
            $attributes = $parameter->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Types::class) {
                    $types = SH::getAttributes($attribute);
                    assert($types instanceof Types);
                    return $types->types;
                }
            }

            $type = $parameter->getType(true);
            assert($type !== null, 'Type is not defined');

            if ($type->isIntersection()) {
                throw new Exception('Intersection types not supported');
            }

            if ($type->isUnion()) {
                $types = [];
                foreach ($type->getTypes() as $subType) {
                    $types[] = Type::from($subType->getSingleName() ?? throw new Exception('Union type must have a single type'));
                }

                return $types;
            }

            return [Type::from($type->getSingleName() ?? throw new Exception('Type must have a single type'))];
        } catch (Exception $exception) {
            throw new Exception('Error in ' . $className . '->' . $parameter->getName() . ': ' . $exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }

    private function addType(Type $type, NamedSchema $schema): void
    {
        if ($type->isClass()) {
            if ($schema->className !== $type->name) {
                throw new Exception('Class name mismatch ' . $schema->className . ' !== ' . $type->name . ' this must be a BUG please report it');
            }

            $class = $this->classLocator->getClass($schema->className) ?? throw new Exception('Class not found ' . $schema->className);
            $this->loopSchema($schema, $class);
            return;
        }

        if ($type->isEmptyArray()) {
            $schema->listElement ??= new NamedSchema(SH::classString($schema->className . '_'));
            return;
        }

        if ($type->isBasicType()) {
            $schema->basicTypes[$type->name] = true;
            return;
        }

        $schema->listElement ??= new NamedSchema(SH::classString($schema->className . '_'));
        $this->addType($type->unpackOnce(), $schema->listElement);
    }
}
