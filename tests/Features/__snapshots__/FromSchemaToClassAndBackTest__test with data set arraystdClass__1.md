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
            "dataKeys": null
        },
        "dataKeys": []
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array",
    "expectedPhpTypeUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    },
    "expectedDocBlockType": "array{}|Data",
    "expectedDocBlockUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    },
    "expectedAttributes": [
        {}
    ],
    "expectedAttributesUses": {
        "Types": "Kanti\\JsonToClass\\Attribute\\Types",
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A extends AbstractJsonReadonlyClass
{
}
````
