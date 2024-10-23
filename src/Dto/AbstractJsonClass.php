<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\Dto;

use JsonSerializable;

/**
 * @api
 */
abstract class AbstractJsonClass implements JsonSerializable
{
    use AbstractJsonClassTrait;
}
