<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use Exception;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Cache\RuntimeCache;
use Kanti\JsonToClass\Dto\Type;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\Helpers\F;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Property;

final readonly class SchemaFromClassCreator
{
    public function __construct(
        private ClassLocator $classLocator,
        private RuntimeCache $cache,
    ) {
    }

    /**
     * @param class-string $className
     */
    public function fromClasses(string $className): ?NamedSchema
    {
        $schema = $this->cache->getClassSchema($className);
        if ($schema) {
            return $schema;
        }

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

        foreach ($class->getProperties() as $property) {
            $dataKey = $this->getDataKey($property);
            $propertyName = $property->getName();
            $types = $this->getTypesFromPhpProperty($property, $schema->className);
            $childClassName = F::getChildClass($schema->className, $propertyName);
            foreach ($types as $type) {
                $schema->properties[$propertyName] ??= new NamedSchema($childClassName, $dataKey);

                $this->addType($type, $schema->properties[$propertyName]);

                if ($property->isInitialized()) {
                    $schema->properties[$propertyName]->canBeMissing = true;
                }

                $readonly = $property->isReadOnly() || $class->isReadOnly();
                $canBeNull = $property->isNullable() || $property->getType(true)?->allows('null');
                if ($readonly && $canBeNull) {
                    $schema->properties[$propertyName]->canBeMissing = true;
                }
            }
        }
    }

    /**
     * @param class-string $className
     * @return list<Type>
     */
    private function getTypesFromPhpProperty(Property $parameter, string $className): array
    {
        try {
            $types = F::getAttribute(Types::class, $parameter);
            if ($types) {
                return $types->types;
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
            $schema->className = F::classString($type->name);
            $class = $this->classLocator->getClass($schema->className) ?? throw new Exception('Class not found ' . $schema->className);
            $this->loopSchema($schema, $class);
            return;
        }

        if ($type->isEmptyArray()) {
            $schema->listElement ??= new NamedSchema(F::classString($schema->className . '_'));
            return;
        }

        if ($type->isBasicType()) {
            $schema->basicTypes[$type->name] = true;
            return;
        }

        $schema->listElement ??= new NamedSchema(F::classString($schema->className . '_'));
        $this->addType($type->unpackOnce(), $schema->listElement);
    }

    private function getDataKey(Property $property): string
    {
        $key = F::getAttribute(Key::class, $property);
        return $key->key ?? $property->getName();
    }
}
