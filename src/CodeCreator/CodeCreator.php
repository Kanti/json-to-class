<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;

final readonly class CodeCreator
{
    public function __construct(
        private Printer $printer,
        private TypeCreator $typeCreator,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function createFiles(NamedSchema $schema): array
    {
        if ($schema->basicTypes) {
            throw new Exception('Basic types not supported at this level ' . json_encode($schema, JSON_THROW_ON_ERROR));
        }

        return $this->createFilesLoop($schema, $schema->className);
    }

    /**
     * @return array<string, string>
     */
    public function createFilesLoop(NamedSchema $schema, string $rootClassName): array
    {
        $resultingClasses = [];

        if ($schema->listElement) {
            $resultingClasses = $this->createFilesLoop($schema->listElement, $rootClassName);
        }

        if ($schema->properties === null) {
            return $resultingClasses;
        }

        $file = new PhpFile();
        $file->setStrictTypes();

        $class = $file
            ->addClass($schema->className)
            ->setFinal()
            ->setReadOnly();

        $namespaces = $file->getNamespaces();
        $namespace = $namespaces[array_key_first($namespaces)] ?? throw new Exception('No namespace found 🤨? ' . $schema->className);

        $namespace->addUse(RootClass::class, Helpers::extractShortName(RootClass::class));
        if ($schema->className === $rootClassName) {
            $class->addAttribute(RootClass::class);
        } else {
            $namespace->addUse($rootClassName, Helpers::extractShortName($rootClassName));
            $class->addAttribute(RootClass::class, [new Literal(Helpers::extractShortName($rootClassName) . '::class')]);
        }

        $constructor = $class
            ->addMethod('__construct')->setPublic();

        foreach ($schema->properties as $name => $property) {
            $phpType = $this->typeCreator->getPhpType($property, $namespace);
            $docBlockType = $this->typeCreator->getDocBlockType($property, $namespace);
            $attribute = $this->typeCreator->getAttribute($property, $namespace);

            $promotedParameter = $constructor
                ->addPromotedParameter($name)
                ->setType($phpType)
                ->setAttributes(array_filter([$attribute]));

            if ($property->canBeMissing) {
                $promotedParameter->setDefaultValue(null);
            }

            if ($docBlockType) {
                $constructor->addComment('@param ' . $docBlockType . ' $' . $name);
            }

            foreach ($this->createFilesLoop($property, $rootClassName) as $className => $classContent) {
                if (isset($resultingClasses[$className])) {
                    throw new Exception('Class already exists ' . $className);
                }

                $resultingClasses[$className] = $classContent;
            }
        }

        if (isset($resultingClasses[$schema->className])) {
            throw new Exception('Class already exists ' . $schema->className);
        }

        $resultingClasses[$schema->className] = $this->printer->printFile($file);

        ksort($resultingClasses);
        uksort($resultingClasses, fn($a, $b): int => strlen($a) <=> strlen($b));
        return $resultingClasses;
    }
}
