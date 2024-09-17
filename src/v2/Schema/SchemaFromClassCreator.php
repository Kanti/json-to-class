<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use Exception;
use Kanti\JsonToClass\v2\Attribute\Types;
use Kanti\JsonToClass\v2\Dto\Type;
use Kanti\JsonToClass\v2\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\v2\Helpers\StringHelpers;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PromotedParameter;
use Throwable;

final readonly class SchemaFromClassCreator
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
        $schema->properties ??= [];

        foreach ($this->getPromotedParameters($class, $schema->className) as $parameter) {
            $propertyName = $parameter->getName();
            $types = $this->getTypesFromPhpProperty($parameter, $schema->className);
            $childClassName = StringHelpers::getChildClass($schema->className, $propertyName);
            foreach ($types as $type) {
                $schema->properties[$propertyName] ??= new NamedSchema($childClassName);

                $this->setType($type, $schema->properties[$propertyName]);

                if ($parameter->hasDefaultValue()) {
                    $schema->properties[$propertyName]->canBeMissing = true;
                }
            }
        }
    }

    private function getClass(string $className): ClassType
    {
        $location = $this->classLocator->getFileLocation($className);
        $content = $this->fileSystem->readContent($location);
        return PhpFile::fromCode($content)->getClasses()[$className] ?? throw new Exception('Class not found');
    }

    /**
     * @return list<PromotedParameter>
     */
    public function getPromotedParameters(ClassType $class, string $className): array
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
     * @return list<Type>
     */
    private function getTypesFromPhpProperty(PromotedParameter $parameter, string $className): array
    {
        try {
            $attributes = $parameter->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Types::class) {
                    $types = StringHelpers::getAttributes($attribute);
                    assert($types instanceof Types);
                    return $types->types;
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

            if ($parameter->hasDefaultValue()) {
                return [Type::from(gettype($parameter->getDefaultValue()))];
            }

            throw new Exception('Can not convert type ' . $type);
        } catch (Exception $exception) {
            throw new Exception('Error in ' . $className . '->' . $parameter->getName() . ': ' . $exception->getMessage(), $exception->getCode(), previous: $exception);
        }
    }

    private function setType(Type $type, NamedSchema $schema): void
    {
        if ($type->isClass()) {
            if ($schema->className !== $type->name) {
                throw new Exception('Class name mismatch ' . $schema->className . ' !== ' . $type->name . ' this must be a BUG please report it');
            }

            $this->loopSchema($schema);
            return;
        }

        if ($type->isEmptyArray()) {
            $schema->listElement ??= new NamedSchema($schema->className . '\\L');
            return;
        }

        if ($type->isBasicType()) {
            $schema->basicTypes[$type->name] = true;
            return;
        }

        if ($type->isArray()) {
            $schema->listElement ??= new NamedSchema($schema->className . '\\L');
            $this->setType($type->unpackOnce(), $schema->listElement);
            return;
        }

        throw new Exception('Unknown type ' . json_encode($type));
    }
}
