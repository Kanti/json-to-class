<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\Property;
use Psr\Log\LoggerInterface;

use function get_debug_type;
use function ksort;
use function uasort;

final readonly class PhpFileUpdater
{
    public function __construct(
        private Printer $printer,
        private TypeCreator $typeCreator,
        private DocBlockUpdater $docblockUpdater,
        private LoggerInterface $logger,
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
        //  add or remove properties/parameters/promotedParameters if not exists
        //  update types for properties/parameters/promotedParameters
        //  update types in Types Attribute
        $this->addProperties($schema, $class, $namespace);

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
        $shortName = Helpers::extractShortName($schema->className);
        $createNewClass = static function () use ($namespace, $shortName): ClassType {

            $namespace->addUse(AbstractJsonReadonlyClass::class);

            return $namespace
                ->addClass($shortName)
                ->setFinal()
                ->setReadOnly()
                ->setExtends(AbstractJsonReadonlyClass::class);
        };

        $class = $namespace->getClasses()[$shortName] ?? $createNewClass();

        F::assertClassType($class, $schema->className);

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

    private function addProperties(NamedSchema $schema, ClassType $class, PhpNamespace $namespace): void
    {
        $properties = $this->sortProperties($schema->properties ?? []);
        foreach ($properties as $name => $propertyConfig) {
            $phpType = $this->typeCreator->getPhpType($propertyConfig, $namespace);
            $docBlockType = $this->typeCreator->getDocBlockType($propertyConfig, $namespace);
            $attributes = $this->typeCreator->getAttributes($name, $propertyConfig, $namespace);

            if ($class->hasProperty($name)) {
                $property = $class->getProperty($name);
            } else {
                $property = $class->addProperty($name);
            }

            $property
                ->setType($phpType)
                ->setAttributes($attributes);

            if ($propertyConfig->canBeMissing) {
                $property->setNullable();

                if (!$class->isReadOnly() && !$property->isReadOnly()) {
                    $property->setValue(null);
                }
            }

            $this->updateDocBlock($property, $phpType, $docBlockType);
        }

        foreach ($class->getProperties() as $property) {
            if (isset($properties[$property->getName()])) {
                continue;
            }

            $this->logger->warning('Removed unnecessary property ' . $property->getName() . ' from ' . $class->getName(), ['properties' => $property->getName(), 'class' => $class->getName()]);
            $class->removeProperty($property->getName());
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

    private function updateDocBlock(Property $property, string $phpType, ?string $docBlockType): void
    {
        $comment = $property->getComment();
        $newComment = $this->docblockUpdater->updateVarBlock($comment, $phpType, $docBlockType);
        $property->setComment($newComment);
    }
}
