<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Container;

use Closure;
use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystem;
use Kanti\JsonToClass\FileSystemAbstraction\FileSystemInterface;
use Kanti\JsonToClass\Logger\StdErrLogger;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\PsrPrinter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

final class JsonToClassContainer implements ContainerInterface
{
    public static ?JsonToClassContainer $instance = null;

    /**
     * @var array<string, Closure|object>
     */
    private readonly array $factories;

    /**
     * @var array<class-string, object>
     */
    private array $instances = [];

    /**
     * @param array<string, Closure|object> $overwriteFactories
     */
    public function __construct(array $overwriteFactories = [])
    {
        $possibleAutoloadLocations = [
            $GLOBALS['_composer_autoload_path'] ?? '',
            __DIR__ . '/../../../autoload.php',
            __DIR__ . '/../../../vendor/autoload.php',
            __DIR__ . '/../../vendor/autoload.php',
            __DIR__ . '/../../../../autoload.php',
        ];

        $this->factories = [
            LoggerInterface::class => fn(): object => new StdErrLogger(),
            FileSystemInterface::class => fn(): object => new FileSystem(),
            Printer::class => fn(): object => new PsrPrinter(),
            ClassLoader::class => fn(): ClassLoader => self::getClassLoader($possibleAutoloadLocations),
            ...$overwriteFactories,
        ];
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        try {
            $this->get($id);
        } catch (ContainerException) {
            return false;
        }

        return true;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function get(string $className): object
    {
        return $this->instances[$className] ??= $this->getInternal($className);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    private function getInternal(string $className): object
    {
        if (isset($this->factories[$className])) {
            return $this->fromFactory($this->factories[$className], $className);
        }

        if (!class_exists($className)) {
            throw new ContainerException('Class ' . $className . ' not found');
        }

        $reflection = new ReflectionClass($className);
        // no constructor no injection
        if (!$reflection->hasMethod('__construct')) {
            return new $className();
        }

        $constructor = $reflection->getMethod('__construct');
        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            /** @var class-string $childClassName */
            $childClassName = (string)$parameter->getType();
            if (!$childClassName) {
                throw new ContainerException('Parameter ' . $className . '->' . $parameter->getName() . ' has no type');
            }

            if (!str_contains($childClassName, '\\')) {
                throw new ContainerException('Parameter ' . $className . '->' . $parameter->getName() . ' type not possible ' . $childClassName);
            }

            $parameters[$parameter->getName()] = $this->get($childClassName);
        }

        return new $className(...$parameters);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    private function fromFactory(object|callable $factory, string $className): object
    {
        if (!is_callable($factory)) {
            if ($factory instanceof $className) {
                return $factory;
            }

            throw new ContainerException('Factory for ' . $className . ' is not callable or instance of ' . $className . ' given: ' . $factory::class);
        }

        $result = $factory();
        if (!is_a($result, $className, false)) {
            $typeInfo = get_debug_type($result);
            throw new ContainerException('Factory for ' . $className . ' dose not produce instance of ' . $className . ' but ' . $typeInfo);
        }

        return $result;
    }

    /**
     * @param list<string> $possibleAutoloadLocations
     * @throws ContainerException
     */
    public static function getClassLoader(array $possibleAutoloadLocations): ClassLoader
    {
        // if defined use this: (composer wraps this file and adds this global:)
        foreach ($possibleAutoloadLocations as $autoloadLocation) {
            if ($autoloadLocation && file_exists($autoloadLocation)) {
                return require $autoloadLocation;
            }
        }

        throw new ContainerException('Autoload file not found 😿');
    }
}
