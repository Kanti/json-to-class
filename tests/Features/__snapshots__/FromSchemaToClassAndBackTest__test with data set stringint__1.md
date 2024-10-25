# Tested "string|int"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": {
            "string": true,
            "int": true
        },
        "listElement": null,
        "dataKeys": null
    },
    "expectedPhpType": "string|int"
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    public string|int $a;
}
````
