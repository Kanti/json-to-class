# Tested "topLevel canBeMissing list<class>|class|null"
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
        "dataKeys": []
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array|null",
    "expectedPhpTypeUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    },
    "expectedDocBlockType": "list<Data_>|Data|null",
    "expectedDocBlockUses": {
        "Data": "Kanti\\GeneratedTest\\Data",
        "Data_": "Kanti\\GeneratedTest\\Data_"
    },
    "expectedAttributes": [
        {}
    ],
    "expectedAttributesUses": {
        "Types": "Kanti\\JsonToClass\\Attribute\\Types",
        "Data": "Kanti\\GeneratedTest\\Data",
        "Data_": "Kanti\\GeneratedTest\\Data_"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\GeneratedTest\Data\A_;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    /** @var list<A_>|A|null */
    #[Types([A_::class], A::class, 'null')]
    public A|array|null $a;
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
