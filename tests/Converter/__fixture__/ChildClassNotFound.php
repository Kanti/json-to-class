<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\AbstractJsonClass;

final class ChildClassNotFound extends AbstractJsonClass
{
    #[Types([ChildClass::class])]
    public array $childClass;
}
