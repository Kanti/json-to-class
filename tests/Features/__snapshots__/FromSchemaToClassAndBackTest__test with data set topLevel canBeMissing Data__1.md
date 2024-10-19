# Tested "topLevel canBeMissing Data"
````json
{
    "schema": {
        "canBeMissing": true,
        "basicTypes": {
            "null": true
        },
        "listElement": null,
        "properties": {
            "int": {
                "canBeMissing": false,
                "basicTypes": {
                    "int": true
                },
                "listElement": null,
                "properties": null
            }
        }
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|null",
    "expectedUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass]
final readonly class Data
{
    use MuteUninitializedPropertyError;

    public A|null $a;
}
````
##### Kanti\GeneratedTest\Data\A:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass(Data::class)]
final readonly class A
{
    use MuteUninitializedPropertyError;

    public int $int;
}
````
