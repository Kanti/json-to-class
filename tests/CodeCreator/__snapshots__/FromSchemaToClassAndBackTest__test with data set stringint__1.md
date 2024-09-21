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
    public function __construct(
        public string|int $a,
    ) {
    }
}
````
