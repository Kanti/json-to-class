# Tested "array{}|stdClass{}"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": {
            "canBeMissing": false,
            "basicTypes": [],
            "listElement": null,
            "properties": null
        },
        "properties": []
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array",
    "expectedDocBlockType": "array{}|Data",
    "expectedAttribute": {},
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
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /** @var array{}|A */
    #[Types([], A::class)]
    public A|array $a;
}
````
##### Kanti\GeneratedTest\Data\A:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A
{
}
````
