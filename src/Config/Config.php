<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Config;

use Kanti\JsonToClass\Config\Dto\AppendSchema;
use Kanti\JsonToClass\Config\Dto\OnExtraProperties;
use Kanti\JsonToClass\Config\Dto\OnInvalidCharacterProperties;
use Kanti\JsonToClass\Config\Dto\OnMissingProperties;
use Kanti\JsonToClass\Config\Dto\RemoveOldClasses;

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

    public function shouldCreateClasses(): bool
    {
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
