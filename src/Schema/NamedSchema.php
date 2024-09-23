<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Schema;

use InvalidArgumentException;
use Kanti\JsonToClass\Helpers\StringHelpers;

final class NamedSchema
{
    public function __construct(
        public string $className,
        public bool $canBeMissing = false,
        /** @var array<string, true> */
        public array $basicTypes = [],
        public ?NamedSchema $listElement = null,
        /** @var array<string, NamedSchema>|null */
        public ?array $properties = null,
    ) {
    }

    public static function fromSchema(string $className, Schema $schema): NamedSchema
    {
        if (!str_contains($className, '\\')) {
            throw new InvalidArgumentException('Class name must contain namespace given: ' . $className);
        }

        $properties = $schema->properties === null ? null : [];
        foreach ($schema->properties ?? [] as $property => $propertySchema) {
            $properties[$property] = self::fromSchema(StringHelpers::getChildClass($className, (string)$property), $propertySchema);
        }

        return new NamedSchema(
            $className,
            $schema->canBeMissing,
            $schema->basicTypes,
            $schema->listElement ? self::fromSchema($className . '\\L', $schema->listElement) : null,
            $properties,
        );
    }

    /**
     * @phpstan-assert-if-true NamedSchema $this->listElement
     */
    public function isOnlyAList(): bool
    {
        if ($this->basicTypes) {
            return false;
        }

        if ($this->properties !== null) {
            return false;
        }

        if ($this->canBeMissing) {
            return false;
        }

        return (bool)$this->listElement;
    }

    public function getFirstNonListChild(): NamedSchema
    {
        $schema = $this;
        while ($schema->isOnlyAList()) {
            $schema = $schema->listElement;
        }

        return $schema;
    }
}
