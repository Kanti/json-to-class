<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use JsonSerializable;

/**
 * @api
 */
abstract readonly class AbstractJsonReadonlyClass implements JsonSerializable
{
    use AbstractJsonClassTrait;
}
