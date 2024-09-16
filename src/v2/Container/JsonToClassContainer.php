<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Container;

use Closure;
use Composer\Autoload\ClassLoader;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystem;
use Kanti\JsonToClass\v2\FileSystemAbstraction\FileSystemInterface;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\PsrPrinter;
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class JsonToClassContainer implements ContainerInterface
{
    /**
     * @var array<string, Closure|object>
     */
    private readonly array $factories;

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    public function __construct(array $overwriteFactories = [])
    {
        $this->factories = [
            ClassLoader::class => fn(): object => require __DIR__ . '/../../../vendor/autoload.php', // TODO fix this path
            FileSystemInterface::class => fn(): object => new FileSystem(getcwd()),
            Printer::class => fn(): object => new PsrPrinter(),
            ...$overwriteFactories,
        ];
    }

    /**
     * @param class-string $id
     * @return bool
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
        $this->instances[$className] ??= $this->getInternal($className);
        return $this->instances[$className];
    }

    /**
     * @template T of object
     * @param class-string<T> $id
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
            $childClassName = $parameter->getType()?->getName();
            if (!$childClassName) {
                throw new ContainerException('Parameter ' . $className . '->' . $parameter->getName() . ' has no type');
            }
            if (str_ends_with($childClassName, 'Interface')) {
                $childClassName = str_replace('Interface', '', $childClassName);
            }

            if (!class_exists($childClassName)) {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }
                throw new ContainerException('Parameter ' . $className . '->' . $parameter->getName() . ' type not possible ' . $childClassName);
            }

            $parameters[$parameter->getName()] = $this->get($childClassName);
        }
        return new $className(...$parameters);
    }

    protected function fromFactory(object|callable $factory, string $className)
    {
        if (is_a($factory, $className, false)) {
            return $factory;
        }
        if (!is_callable($factory)) {
            throw new ContainerException('Factory for ' . $className . ' is not callable or instance of ' . $className);
        }
        $result ??= $factory();
        if (!$result instanceof $className) {
            throw new ContainerException('Factory for ' . $className . ' dose not produce instance of ' . $className . ' but ' . is_object($result) ? get_class($result) : gettype($result));
        }
        return $result;
    }
}
