# Tested "topLevel canBeMissing Data"
````json
{
    "schema": {
        "canBeMissing": true,
        "basicTypes": {
            "null": true
        },
        "listElement": null,
        "dataKeys": {
            "int": {
                "canBeMissing": false,
                "basicTypes": {
                    "int": true
                },
                "listElement": null,
                "dataKeys": null
            }
        }
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|null",
    "expectedPhpTypeUses": {
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A extends AbstractJsonReadonlyClass
{
    public int $int;
}
````
