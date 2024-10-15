<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Attribute;

use Attribute;
use Kanti\JsonToClass\Dto\Type;

/**
 * usage:
 *
 *   #[Types(Property::class, [Property1::class], [], 'string', 'float', 'int', 'bool', 'null')]
 *   public Property|array|string|float|int|bool|null $property = null;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Types
{
    /**
     * @var list<Type>
     */
    public array $types;

    /**
     * @param string|array{}|list<string>|list<list<string>>|list<list<list<string>>> ...$types
     */
    public function __construct(
        string|array ...$types,
    ) {
        $this->types = array_map(Type::from(...), array_values($types));
    }
}
