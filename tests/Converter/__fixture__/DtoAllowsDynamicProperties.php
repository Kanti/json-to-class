<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Tests\Converter\__fixture__;

use AllowDynamicProperties;
use Kanti\JsonToClass\Dto\AbstractJsonClass;

#[AllowDynamicProperties]
final class DtoAllowsDynamicProperties extends AbstractJsonClass
{
    public ?string $definedProperty;
}
