<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Config;

use Kanti\JsonToClass\v2\Config\Dto\AppendSchema;
use Kanti\JsonToClass\v2\Config\Dto\OnExtraProperties;
use Kanti\JsonToClass\v2\Config\Dto\OnInvalidCharacterProperties;
use Kanti\JsonToClass\v2\Config\Dto\OnMissingProperties;

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
