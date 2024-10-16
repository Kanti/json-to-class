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
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

use function get_debug_type;
use function is_array;

/**
 * @api
 */
#[Autoconfigure(public: true, autowire: true)] // this does not work (for TYPO3) :(
final readonly class Converter
{
    public function __construct(
        private Validator $validator,
        private ClassCreator $classCreator,
        private ClassMapper $classMapper,
        private LoggerInterface $logger,
    ) {
    }

    public static function getInstance(?ContainerInterface $container = null): self
    {
        static $staticContainer = new JsonToClassContainer();
        return ($container ?? $staticContainer)->get(self::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return list<T>
     */
    public function jsonDecodeList(string $className, string $json, Config $config = new SaneConfig()): array
    {
        $this->logger->debug('jsonDecode', ['className' => $className, 'json' => $json]);
        $data = json_decode($json, associative: false, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON given: "' . get_debug_type($data) . '" allowed: array');
        }

        return $this->convertList($className, $data, $config);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     *
     * @return T
     */
    public function jsonDecode(string $className, string $json, Config $config = new SaneConfig()): object
    {
        $this->logger->debug('jsonDecode', ['className' => $className, 'json' => $json]);
        $data = json_decode($json, associative: false, flags: JSON_THROW_ON_ERROR);
        if (!$data instanceof stdClass) {
            throw new InvalidArgumentException('Invalid JSON given: "' . get_debug_type($data) . '" allowed: object');
        }

        return $this->convert($className, $data, $config);
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
        $this->logger->debug('convert', ['className' => $className, 'data' => $data]);
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace');
        }

        if (is_array($data) && array_is_list($data)) {
            throw new InvalidArgumentException('If you want to convert an array of objects, use convertList method instead');
        }

        $this->validator->validateData($data, $config);

        $shouldCreateClasses = $config->shouldCreateClasses();
        $this->logger->debug('shouldCreateClasses', ['shouldCreateClasses' => $shouldCreateClasses]);
        if ($shouldCreateClasses) {
            $this->classCreator->createClasses($className, $data, $config);
        }

        return $this->classMapper->map($className, $data, $config);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     * @param list<array<string, mixed>|stdClass> $data
     * @phpstan-param array<array<mixed>|stdClass> $data
     *
     * @return list<T>
     */
    public function convertList(string $className, array $data, Config $config = new SaneConfig()): array
    {
        $this->logger->debug('convertList', ['className' => $className, 'data' => $data]);
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace');
        }

        if (!array_is_list($data)) {
            throw new InvalidArgumentException('If you want to convert an object, use convert method instead');
        }

        $this->validator->validateData($data, $config);

        $shouldCreateClasses = $config->shouldCreateClasses();
        $this->logger->debug('shouldCreateClasses', ['shouldCreateClasses' => $shouldCreateClasses]);
        if ($shouldCreateClasses) {
            $this->classCreator->createClasses($className, $data, $config);
        }

        $result = [];
        foreach ($data as $item) {
            $result[] = $this->classMapper->map($className, $item, $config);
        }

        return $result;
    }
}
