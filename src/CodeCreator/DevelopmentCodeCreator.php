<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use AllowDynamicProperties;
use Exception;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\DataInterface;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Log\LoggerInterface;

use function array_values;
use function class_exists;
use function Safe\class_implements;

final class DevelopmentCodeCreator
{
    /** @var array<class-string, list<Property>> */
    private static array $properties = [];

    public function __construct(
        private readonly TypeCreator $typeCreator,
        private readonly ClassLocator $classLocator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createDevelopmentClasses(NamedSchema $schema): void
    {
        $this->logger->debug('createDevelopmentClasses', ['schema' => $schema->className]);

        if ($schema->listElement) {
            $this->createDevelopmentClasses($schema->listElement);
        }

        if (!$schema->properties) {
            return;
        }

        $parameters = [];
        foreach ($schema->properties ?? [] as $name => $property) {
            $this->createDevelopmentClasses($property);

            $types = $this->typeCreator->getAttributeTypes($property, null);
            $parameters[] = new Property($name, (new Types(...$types))->types, $property->canBeMissing);
        }

        $this->setClassProperties($schema->className, ...$parameters);

        $this->createDevelopmentClassIfNotExists($schema->className);
    }

    /**
     * @param class-string $className
     */
    private function createDevelopmentClassIfNotExists(string $className): void
    {
        $this->logger->debug('createDevelopmentClassIfNotExists', ['schema' => $className]);
        if (class_exists($className, false)) {
            if (self::isDevelopmentDto($className)) {
                return;
            }

            throw new Exception(sprintf("Class %s already exists and is not a %s", $className, DataInterface::class));
        }

        $shortName = Helpers::extractShortName($className);
        $namespace = Helpers::extractNamespace($className);

        $phpClass = $this->classLocator->getClass($className) ?? (new ClassType($shortName, new PhpNamespace($namespace)))->setFinal();

        $phpClass->setReadOnly(false);

        foreach ($phpClass->getProperties() as $property) {
            $property->setType('mixed');
        }

        $phpClass->addImplement(DataInterface::class);
        $hasAllowDynamicProperties = false;
        foreach ($phpClass->getAttributes() as $attribute) {
            if ($attribute->getName() === AllowDynamicProperties::class) {
                $hasAllowDynamicProperties = true;
                break;
            }
        }

        if (!$hasAllowDynamicProperties) {
            $phpClass->addAttribute(AllowDynamicProperties::class);
        }

        $eval = "namespace " . $namespace . ";\n" . $phpClass;
        $this->runEval($eval);

        if (self::isDevelopmentDto($className)) {
            return;
        }

        throw new Exception(sprintf("Class %s created, but is not a DataTrait", $className));
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @phpstan-assert-if-true class-string<DataInterface> $className
     */
    public static function isDevelopmentDto(string $className): bool
    {
        if (!class_exists($className, false)) {
            return false;
        }

        return isset(class_implements($className, false)[DataInterface::class]);
    }

    /**
     * @param class-string $className
     */
    private function setClassProperties(string $className, Property ...$properties): void
    {
        self::$properties[$className] = array_values($properties);
    }

    /**
     * @param class-string $className
     * @return list<Property>
     */
    public static function getClassProperties(string $className): array
    {
        return self::$properties[$className] ?? [];
    }

    private function runEval(string $phpCode): void
    {
        eval($phpCode);
    }
}
