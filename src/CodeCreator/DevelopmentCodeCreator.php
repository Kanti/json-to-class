<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use AllowDynamicProperties;
use Exception;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Cache\RuntimeCache;
use Kanti\JsonToClass\Dto\AbstractJsonClass;
use Kanti\JsonToClass\Dto\DevelopmentFakeClassInterface;
use Kanti\JsonToClass\Dto\Property;
use Kanti\JsonToClass\FileSystemAbstraction\ClassLocator;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function class_exists;
use function Safe\class_implements;
use function sprintf;

final readonly class DevelopmentCodeCreator
{
    public function __construct(
        private TypeCreator $typeCreator,
        private ClassLocator $classLocator,
        private LoggerInterface $logger,
        private RuntimeCache $cache,
    ) {
    }

    public function createOrUpdateDevelopmentClasses(NamedSchema $schema): void
    {
        $this->logger->debug('createDevelopmentClasses', ['schema' => $schema->className]);

        if ($schema->listElement) {
            $this->createOrUpdateDevelopmentClasses($schema->listElement);
        }

        if (!$schema->properties) {
            return;
        }

        $parameters = [];
        $mapping = [];
        foreach ($schema->properties ?? [] as $name => $property) {
            $this->createOrUpdateDevelopmentClasses($property);

            $types = $this->typeCreator->getAttributeTypes($property, null);
            $mapping[$name] = $property->dataKey ?? $name;
            $parameters[] = new Property($name, $mapping[$name], (new Types(...$types))->types, $property->canBeMissing);
        }

        $this->cache->setClassProperties($schema->className, ...$parameters);
        $this->cache->setClassSchema($schema->className, $schema);
        RuntimeCache::setPropertyMapping($schema->className, $mapping);

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

            throw new Exception(sprintf("Class %s already exists and is not a %s", $className, DevelopmentFakeClassInterface::class));
        }


        $phpFile = $this->classLocator->getClassFile($className) ?? (new PhpFile())->setStrictTypes();
        $phpClass = $phpFile->getClasses()[$className]
            ?? $phpFile
                ->addClass($className)
                ->setFinal();
        if (!$phpClass instanceof ClassType) {
            throw new RuntimeException('Class ' . $className . ' not found it is a ' . $phpClass::class);
        }

        $phpClass
            ->setReadOnly(false)
            ->setExtends(AbstractJsonClass::class);

        foreach ($phpClass->getProperties() as $property) {
            $property->setType('mixed');
        }

        $phpClass->addImplement(DevelopmentFakeClassInterface::class);
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

        $namespace = Helpers::extractNamespace($className);
        $phpNamespace = $phpFile->getNamespaces()[$namespace] ?? throw new Exception(sprintf('Namespace %s not found', $namespace));
        $this->runEval($phpNamespace->__toString());

        if (self::isDevelopmentDto($className)) {
            return;
        }

        throw new Exception(sprintf("Class %s created, but is not a DataTrait", $className));
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @phpstan-assert-if-true class-string<DevelopmentFakeClassInterface> $className
     */
    public static function isDevelopmentDto(string $className): bool
    {
        if (!class_exists($className, false)) {
            return false;
        }

        return isset(class_implements($className, false)[DevelopmentFakeClassInterface::class]);
    }

    /**
     * so the eval code does not have write access to the current scope
     */
    private function runEval(string $phpCode): void
    {
        eval($phpCode);
    }
}
