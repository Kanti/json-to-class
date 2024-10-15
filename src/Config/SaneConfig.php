<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

use Kanti\JsonToClass\Config\Enums\AppendSchema;
use Kanti\JsonToClass\Config\Enums\OnExtraProperties;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Config\Enums\OnMissingProperties;
use Kanti\JsonToClass\Config\Enums\ShouldCreateClasses;

final readonly class SaneConfig extends Config
{
    public function __construct(
        public OnExtraProperties $onExtraProperties = OnExtraProperties::IGNORE,
        public OnMissingProperties $onMissingProperties = OnMissingProperties::THROW_EXCEPTION,
        public OnInvalidCharacterProperties $onInvalidCharacterProperties = OnInvalidCharacterProperties::TRY_PREFIX_WITH_UNDERSCORE,
        public AppendSchema $appendSchema = AppendSchema::APPEND,
        public ShouldCreateClasses $shouldCreateClasses = ShouldCreateClasses::TRY_TO_DETECT,
    ) {
    }
}
