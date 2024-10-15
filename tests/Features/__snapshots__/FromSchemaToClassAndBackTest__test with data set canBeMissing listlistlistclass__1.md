# Tested "canBeMissing list<list<list<class>>>"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": {
            "canBeMissing": false,
            "basicTypes": [],
            "listElement": {
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
            "properties": null
        },
        "properties": null
    },
    "expectedPhpType": "array",
    "expectedDocBlockType": "list<list<list<Data___>>>",
    "expectedAttribute": {},
    "expectedUses": {
        "Data___": "Kanti\\GeneratedTest\\Data___"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A___;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /** @var list<list<list<A___>>> */
    #[Types([[[A___::class]]])]
    public array $a;
}
````
##### Kanti\GeneratedTest\Data\A___:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A___
{
    public int $int;
}
````
