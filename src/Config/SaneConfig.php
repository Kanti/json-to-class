<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

use Kanti\JsonToClass\Config\Dto\AppendSchema;
use Kanti\JsonToClass\Config\Dto\OnExtraProperties;
use Kanti\JsonToClass\Config\Dto\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Config\Dto\OnMissingProperties;

final readonly class SaneConfig extends Config
{
    public function __construct(
        public OnExtraProperties $onExtraProperties = OnExtraProperties::IGNORE,
        public OnMissingProperties $onMissingProperties = OnMissingProperties::THROW_EXCEPTION,
        public OnInvalidCharacterProperties $onInvalidCharacterProperties = OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE,
        public AppendSchema $appendSchema = AppendSchema::APPEND,
    ) {
    }
}
