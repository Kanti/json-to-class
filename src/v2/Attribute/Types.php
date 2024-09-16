<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Attribute;

use Attribute;
use Kanti\JsonToClass\v2\Dto\Type;

/**
 * usage:
 *
 * public function __construct(
 *   #[Types(Property::class, [Property1::class], [], 'string', 'float', 'int', 'bool', 'null')]
 *   public Property|array|string|float|int|bool|null $property = null,
 * ) {}
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Types
{
    /**
     * @var list<Type>
     */
    public array $types;

    /**
     * @param string|list<string|list<mixed>>|array{} ...$types
     */
    public function __construct(
        string|array ...$types,
    ) {
        $this->types = array_map(Type::from(...), $types);
    }
}
