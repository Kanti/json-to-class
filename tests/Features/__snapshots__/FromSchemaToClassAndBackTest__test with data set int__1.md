# Tested "int"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": {
            "int": true
        },
        "listElement": null,
        "properties": null
    },
    "expectedPhpType": "int"
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
    public int $a;
}
````
