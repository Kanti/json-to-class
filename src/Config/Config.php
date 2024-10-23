<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

use Kanti\JsonToClass\Config\Enums\AppendSchema;
use Kanti\JsonToClass\Config\Enums\OnExtraProperties;
use Kanti\JsonToClass\Config\Enums\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Config\Enums\OnMissingProperties;
use Kanti\JsonToClass\Config\Enums\RemoveOldClasses;
use Kanti\JsonToClass\Config\Enums\ShouldCreateClasses;
use Kanti\JsonToClass\Config\Enums\ShouldCreateDevelopmentClasses;

/**
 * todo this should be an interface (with PHP 8.4 and property hooks it should be possible to refactor this)
 */
abstract readonly class Config
{
    public OnExtraProperties $onExtraProperties;

    public OnMissingProperties $onMissingProperties;

    public OnInvalidCharacterProperties $onInvalidCharacterProperties;

    public AppendSchema $appendSchema;

    /** @var RemoveOldClasses TODO implement this feature */
    public RemoveOldClasses $removeOldClasses;

    public ShouldCreateClasses $shouldCreateClasses;

    public ShouldCreateDevelopmentClasses $shouldCreateDevelopmentClasses;

    public function shouldCreateClasses(): bool
    {
        if ($this->shouldCreateClasses === ShouldCreateClasses::YES) {
            return true;
        }

        if ($this->shouldCreateClasses === ShouldCreateClasses::NO) {
            return false;
        }

        $config = getenv('JSON_TO_CLASS_CREATE');
        if ($config === 'create') {
            return true;
        }

        if ($config === 'no') {
            return false;
        }

        if (getenv('IS_DDEV_PROJECT')) {
            return true;
        }

        if (getenv('TYPO3_CONTEXT') === 'Development/docker') {
            return true;
        }

        if (getenv('APP_ENV') === 'dev') {
            return true;
        }

        return getenv('APP_ENV') === 'local';
    }
}
