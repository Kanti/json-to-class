# Tested "string|int diffrent sorting"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": {
            "int": true,
            "string": true
        },
        "listElement": null,
        "properties": null
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

#[RootClass]
final readonly class Data
{
    public string|int $a;
}
````
