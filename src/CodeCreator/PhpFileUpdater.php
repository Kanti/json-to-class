<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;

use function array_filter;
use function get_debug_type;
use function ksort;
use function uasort;

final readonly class PhpFileUpdater
{
    public function __construct(
        private Printer $printer,
        private TypeCreator $typeCreator,
        private DocBlockUpdater $docblockUpdater,
    ) {
    }

    public function updateFile(string $rootClassName, NamedSchema $schema, PhpFile $file): string
    {
        $isEmptyFile = (string)$file === (string)(new PhpFile());
        if ($isEmptyFile) {
            $file->setStrictTypes();
        }

        //  add namespace if not exists
        $namespace = $this->addNamespace($schema, $file);
        //  add class if not exists
        $class = $this->addClass($schema, $namespace);
        //  add RootClass Attribute if not exists
        $this->addRootClassAttribute($schema, $namespace, $class, $rootClassName);
        //  add constructor if not exists
        //  make constructor public if not public
        $constructor = $this->addConstructor($class);
        //  add or remove properties/parameters/promotedParameters if not exists
        //  update types for properties/parameters/promotedParameters
        //  update types in Types Attribute
        $this->addParameters($schema, $constructor, $namespace);

        return $this->printer->printFile($file);
    }

    private function addNamespace(NamedSchema $schema, PhpFile $file): PhpNamespace
    {
        $namespaceName = Helpers::extractNamespace($schema->className);
        return $file->getNamespaces()[$namespaceName]
            ?? $file->addNamespace($namespaceName);
    }

    private function addClass(NamedSchema $schema, PhpNamespace $namespace): ClassType
    {
        $className = Helpers::extractShortName($schema->className);
        $class = $namespace->getClasses()[$className]
            ?? $namespace
                ->addClass($className)
                ->setFinal()
                ->setReadOnly();
        if (!$class instanceof ClassType) {
            throw new Exception(sprintf('Expected ClassType, got %s for class %s', get_debug_type($class), $schema->className));
        }

        return $class;
    }

    private function addRootClassAttribute(NamedSchema $schema, PhpNamespace $namespace, ClassType $class, string $rootClassName): void
    {
        foreach ($class->getAttributes() as $attribute) {
            if ($attribute->getName() === RootClass::class) {
                return;
            }
        }

        $namespace->addUse(RootClass::class);
        if ($schema->className === $rootClassName) {
            $class->addAttribute(RootClass::class);
            return;
        }

        $namespace->addUse($rootClassName);
        $class->addAttribute(RootClass::class, [new Literal(Helpers::extractShortName($rootClassName) . '::class')]);
    }

    private function addConstructor(ClassType $class): Method
    {
        if ($class->hasMethod('__construct')) {
            return $class->getMethod('__construct')->setPublic();
        }

        return $class->addMethod('__construct')->setPublic();
    }

    private function addParameters(NamedSchema $schema, Method $constructor, PhpNamespace $namespace): void
    {
        $properties = $this->sortProperties($schema->properties ?? []);
        foreach ($properties as $name => $property) {
            $phpType = $this->typeCreator->getPhpType($property, $namespace);
            $docBlockType = $this->typeCreator->getDocBlockType($property, $namespace);
            $attribute = $this->typeCreator->getAttribute($property, $namespace);

            if ($constructor->hasParameter($name)) {
                $parameter = $constructor->getParameter($name);
            } else {
                $parameter = $constructor->addPromotedParameter($name);
            }

            $parameter
                ->setType($phpType)
                ->setAttributes(array_filter([$attribute]));

            if ($property->canBeMissing) {
                $parameter->setDefaultValue(null);
            }

            $this->addDocblock($constructor, $name, $phpType, $docBlockType);
        }

        foreach ($constructor->getParameters() as $parameter) {
            if (isset($properties[$parameter->getName()])) {
                continue;
            }

            $constructor->removeParameter($parameter->getName());
            $this->addDocblock($constructor, $parameter->getName(), null, null);
        }
    }

    /**
     * @param array<string, NamedSchema> $properties
     * @return array<string, NamedSchema>
     */
    private function sortProperties(array $properties): array
    {
        ksort($properties);
        uasort($properties, fn(NamedSchema $a, NamedSchema $b): int => $a->canBeMissing <=> $b->canBeMissing);
        return $properties;
    }

    private function addDocblock(Method $constructor, string $name, ?string $phpType, ?string $docBlockType): void
    {
        $comment2 = $this->docblockUpdater->updateDocblock(
            $constructor->getComment() ?? '',
            $name,
            $phpType,
            $docBlockType,
        );
        if ($comment2) {
            $constructor->setComment($comment2);
        }
    }
}
