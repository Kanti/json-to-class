<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\CodeCreator;

use Exception;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\DataInterface;
use Kanti\JsonToClass\Dto\Parameter;
use Kanti\JsonToClass\Schema\NamedSchema;
use Nette\PhpGenerator\Helpers;

use function Safe\class_implements;
use function class_exists;

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

        $parameters = [];
        foreach ($schema->properties ?? [] as $name => $property) {
            $this->createDevelopmentClasses($property);

            $types = $this->typeCreator->getAttributeTypes($property, null);
            $parameters[] = new Parameter($name, (new Types(...$types))->types, $property->canBeMissing);
        }

        $classNameImplementation = $this->createDevelopmentClassIfNotExists($schema->className);
        $classNameImplementation::setClassParameters(...$parameters);
    }

    /**
     * @param class-string $className
     * @return class-string<DataInterface>
     */
    private function createDevelopmentClassIfNotExists(string $className): string
    {
        if (class_exists($className, false)) {
            if (self::isDevelopmentDto($className)) {
                return $className;
            }

            throw new Exception(sprintf("Class %s already exists and is not a DataTrait", $className));
        }

        $shortName = Helpers::extractShortName($className);
        $namespace = Helpers::extractNamespace($className);
        eval(
            <<<PHP
            namespace {$namespace} {
                #[\AllowDynamicProperties]
                class {$shortName} implements \Kanti\JsonToClass\Dto\DataInterface {
                    use \Kanti\JsonToClass\Dto\DataTrait;
                }
            }
            PHP
        );
        if (!self::isDevelopmentDto($className)) {
            throw new Exception(sprintf("Class %s created, but is not a DataTrait", $className));
        }

        return $className;
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
}
