# Tested "canBeMissing list<class>"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": {
            "canBeMissing": false,
            "basicTypes": [],
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
        "dataKeys": null
    },
    "expectedPhpType": "array",
    "expectedDocBlockType": "list<Data_>",
    "expectedUses": {
        "Data_": "Kanti\\GeneratedTest\\Data_"
    },
    "expectedAttributes": [
        {}
    ],
    "expectedUsesAttributes": {
        "Types": "Kanti\\JsonToClass\\Attribute\\Types",
        "Data_": "Kanti\\GeneratedTest\\Data_"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A_;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    /** @var list<A_> */
    #[Types([A_::class])]
    public array $a;
}
````
##### Kanti\GeneratedTest\Data\A_:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A_ extends AbstractJsonReadonlyClass
{
    public int $int;
}
````
