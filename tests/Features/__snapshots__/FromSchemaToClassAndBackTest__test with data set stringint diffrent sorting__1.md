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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass]
final readonly class Data
{
    use MuteUninitializedPropertyError;

    public string|int $a;
}
````
