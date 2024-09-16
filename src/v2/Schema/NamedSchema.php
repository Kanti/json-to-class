<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use Nette\PhpGenerator\Helpers;

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
            throw new \InvalidArgumentException('Class name must contain namespace');
        }
        $properties = $schema->properties === null ? null : [];
        foreach ($schema->properties ?? [] as $property => $propertySchema) {
            $className2 = ucfirst($property);
            if (Helpers::Keywords[strtolower($property)] ?? false) {
                $className2 = '_' . $className2;
            }
            $properties[$property] = self::fromSchema($className . '\\' . $className2, $propertySchema);
        }
        return new NamedSchema(
            $className,
            $schema->canBeMissing,
            $schema->basicTypes,
            $schema->listElement ? self::fromSchema($className . '\\L', $schema->listElement) : null,
            $properties,
        );
    }

    public function isOnlyAList(): bool
    {
        if ($this->basicTypes) {
            return false;
        }
        if ($this->properties === null) {
            return false;
        }
        if ($this->canBeMissing) {
            return false;
        }
        return (bool)$this->listElement;
    }
}
