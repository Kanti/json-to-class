# Tested "list<class>"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": {
            "canBeMissing": false,
            "basicTypes": [],
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
        "properties": null
    },
    "expectedPhpType": "array",
    "expectedDocBlockType": "list<Data_>",
    "expectedAttribute": {},
    "expectedUses": {
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

#[RootClass]
final readonly class Data
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

#[RootClass(Data::class)]
final readonly class A_
{
    public int $int;
}
````
