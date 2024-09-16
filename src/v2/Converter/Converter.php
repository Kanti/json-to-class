<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Converter;

use Kanti\JsonToClass\v2\ClassCreator\ClassCreatorInterface;
use Kanti\JsonToClass\v2\Config\Config;
use Kanti\JsonToClass\v2\Config\SaneConfig;
use Kanti\JsonToClass\v2\Container\JsonToClassContainer;
use Kanti\JsonToClass\v2\Validator\Validator;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @api
 */
#[Autoconfigure(public: true)]
final readonly class Converter
{
    public function __construct(
        private Validator $validator,
        private ClassCreatorInterface $classCreator,
    ) {
    }

    public static function getInstance(?ContainerInterface $container = null): self
    {
        $container ??= new JsonToClassContainer();
        return $container->get(self::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed>|stdClass<mixed> $data
     * @phpstan-param array<mixed>|stdClass<mixed> $data
     *
     * @return T
     */
    public function convert(string $className, array|stdClass $data, Config $config = new SaneConfig()): object
    {
        if (!str_contains($className, '\\')) {
            throw new \InvalidArgumentException('Class name must contain namespace');
        }

        $this->validator->validateData($data, $config);

        if ($config->shouldCreateClasses()) {
            $this->classCreator->createClasses($className, $data, $config);
        }

        return $this->convertToClass($className, $data, $config);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param array<string, mixed>|stdClass<mixed> $data
     * @phpstan-param array<mixed>|stdClass<mixed> $data
     *
     * @return T
     */
    private function convertToClass(string $className, array|stdClass $data, Config $config): object
    {
        return new $className(...$data);
    }
}
