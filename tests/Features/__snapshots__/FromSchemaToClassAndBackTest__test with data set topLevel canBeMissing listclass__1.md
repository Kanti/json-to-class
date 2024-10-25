# Tested "topLevel canBeMissing list<class>"
````json
{
    "schema": {
        "canBeMissing": true,
        "basicTypes": {
            "null": true
        },
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
    "expectedPhpType": "array|null",
    "expectedDocBlockType": "list<Data_>|null",
    "expectedDocBlockUses": {
        "Data_": "Kanti\\GeneratedTest\\Data_"
    },
    "expectedAttributes": [
        {}
    ],
    "expectedAttributesUses": {
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
    /** @var list<A_>|null */
    #[Types([A_::class], 'null')]
    public array|null $a;
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
