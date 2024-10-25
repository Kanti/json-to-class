<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Helpers\F;
use Kanti\JsonToClass\Mapper\NameMapper;

use function array_flip;
use function array_keys;

final readonly class SchemaToNamedSchemaConverter
{
    public function __construct(private NameMapper $nameMapper)
    {
    }

    /**
     * @param class-string $className
     */
    public function convert(string $className, Schema $schema, ?string $dataKey = null): NamedSchema
    {
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace given: ' . $className);
        }

        $properties = $this->convertProperties($schema, $className);

        return new NamedSchema(
            $className,
            $dataKey,
            $schema->canBeMissing,
            $schema->basicTypes,
            $schema->listElement ? $this->convert(F::classString($className . '_'), $schema->listElement) : null,
            $properties,
        );
    }

    /**
     * @param class-string $className
     * @return ?array<string, NamedSchema>
     */
    private function convertProperties(Schema $schema, string $className): ?array
    {
        if ($schema->dataKeys === null) {
            return null;
        }

        $propertyNames = $this->nameMapper->map(array_keys($schema->dataKeys), []);
        $dataKeys = array_flip($propertyNames);

        $properties = [];
        foreach ($schema->dataKeys ?? [] as $dataKey => $propertySchema) {
            $propertyName = $dataKeys[$dataKey];
            $properties[$propertyName] = $this->convert(F::getChildClass($className, (string)$propertyName), $propertySchema, $dataKey);
        }

        return $properties;
    }
}
