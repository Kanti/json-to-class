<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

use Kanti\JsonToClass\Config\Enums\AppendSchema;
use Kanti\JsonToClass\Config\Enums\OnExtraProperties;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Config\Enums\OnMissingProperties;

final readonly class StrictConfig extends Config
{
    public function __construct(
        public OnExtraProperties $onExtraProperties = OnExtraProperties::THROW_EXCEPTION,
        public OnMissingProperties $onMissingProperties = OnMissingProperties::THROW_EXCEPTION,
        public OnInvalidCharacterProperties $onInvalidCharacterProperties = OnInvalidCharacterProperties::THROW_EXCEPTION,
        public AppendSchema $appendSchema = AppendSchema::OVERRIDE,
    ) {
    }
}
