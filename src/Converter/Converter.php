<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Converter;

use InvalidArgumentException;
use Kanti\JsonToClass\ClassCreator\ClassCreator;
use Kanti\JsonToClass\Config\Config;
use Kanti\JsonToClass\Config\SaneConfig;
use Kanti\JsonToClass\Container\JsonToClassContainer;
use Kanti\JsonToClass\Validator\Validator;
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
        private ClassCreator $classCreator,
        private ClassMapper $classMapper,
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
     * @param array<string, mixed>|stdClass $data
     * @phpstan-param array<mixed>|stdClass $data
     *
     * @return T
     */
    public function convert(string $className, array|stdClass $data, Config $config = new SaneConfig()): object
    {
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace');
        }

        $this->validator->validateData($data, $config);

        if ($config->shouldCreateClasses()) {
            $this->classCreator->createClasses($className, $data, $config);
        }

        return $this->classMapper->map($className, $data, $config);
    }
}