<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\v2\Schema;

use stdClass;

interface SchemaFromDataCreatorInterface
{
    public function fromData(array|stdClass $data): Schema;
}
