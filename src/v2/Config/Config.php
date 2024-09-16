<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Config;

use Kanti\JsonToClass\v2\Config\Dto\AppendSchema;
use Kanti\JsonToClass\v2\Config\Dto\OnExtraProperties;
use Kanti\JsonToClass\v2\Config\Dto\OnInvalidCharacterProperties;
use Kanti\JsonToClass\v2\Config\Dto\OnMissingProperties;

readonly class Config
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
        if (getenv('APP_ENV') === 'local') {
            return true;
        }

        return false;
    }
}
