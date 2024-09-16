<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use Exception;
use Kanti\JsonToClass\v2\Attribute\Types;
use Kanti\JsonToClass\v2\Dto\Type;
use Kanti\JsonToClass\v2\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PromotedParameter;

final class SchemaFromClassCreator
{
    public function __construct(
        private ClassLocator $classLocator,
        private FileSystemInterface $fileSystem,
    ) {
    }

    public function fromClasses(string $className): NamedSchema
    {
        $schema = new NamedSchema($className);
        $this->loopSchema($schema);
        return $schema;
    }

    private function loopSchema(NamedSchema $schema): void
    {
        $class = $this->getClass($schema->className);
        $propertyTypes = $this->getTypesFromPhpClass($class, $schema->className);
        foreach ($propertyTypes as $propertyName => $types) {
            $childClassName = $schema->className . '\\' . ucfirst($propertyName); // TODO: this needs to use a central place for this!!
            foreach ($types as $type) {
                $schema->properties[$propertyName] ??= new NamedSchema($childClassName);

                // TODO
            }
        }
    }

    private function getClass(string $className): ClassType
    {
        $location = $this->classLocator->getFileLocation($className);
        $content = $this->fileSystem->readContent($location);
        return PhpFile::fromCode($content)->getClasses()[0] ?? throw new Exception('Class not found');
    }

    /**
     * @param ClassType $class
     * @return array<string, list<Type>>
     */
    private function getTypesFromPhpClass(ClassType $class, string $className): array
    {
        $types = [];
        $method = $class->getMethod('__construct');
        foreach ($method->getParameters() as $parameter) {
            if (!$parameter instanceof PromotedParameter) {
                throw new Exception('Parameter is not a PromotedParameter ' . $className . '->' . $parameter->getName());
            }
            $types[$parameter->getName()] = $this->getTypesFromPhpProperty($parameter);
        }
        return $types;
    }

    /**
     * @return list<Type>
     */
    private function getTypesFromPhpProperty(PromotedParameter $parameter): array
    {
        $attributes = $parameter->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Types::class) {
                $types = $attribute->getArguments();
                return (new Types(...$types))->types;
            }
        }
        $type = $parameter->getType(true);
        if ($type->isIntersection()) {
            throw new Exception('Intersection types not supported');
        }

        if ($type->isUnion()) {
            $types = [];
            foreach ($type->getTypes() as $subType) {
                $types[] = Type::from($subType->getSingleName());
            }
            return $types;
        }

        if ($type->isSimple()) {
            return [Type::from($type->getSingleName())];
        }

        throw new Exception('Can not convert type ' . $type);
    }

    protected function setType(Type $type, NamedSchema $schema): void
    {
        if ($type->isClass()) {
            return;
        }
        if ($type->isBasicType()) {
            $schema->basicTypes[$type->name] = true;
            return;
        }
        if ($type->isArray()) {
            $schema->listElement = new NamedSchema($schema->className . '\\L');
            $this->setType($type->unpackOnce(), $schema->listElement);
            return;
        }
        throw new Exception('Unknown type ' . json_encode($type));
    }
}
