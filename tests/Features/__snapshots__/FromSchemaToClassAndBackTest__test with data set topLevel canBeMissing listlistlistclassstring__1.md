# Tested "topLevel canBeMissing list<list<list<class>>|string>"
````json
{
    "schema": {
        "canBeMissing": true,
        "basicTypes": {
            "null": true
        },
        "listElement": {
            "canBeMissing": false,
            "basicTypes": {
                "string": true
            },
            "listElement": {
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
            "dataKeys": null
        },
        "dataKeys": null
    },
    "expectedPhpType": "array|null",
    "expectedDocBlockType": "list<list<list<Data___>>|string>|null",
    "expectedDocBlockUses": {
        "Data___": "Kanti\\GeneratedTest\\Data___"
    },
    "expectedAttributes": [
        {}
    ],
    "expectedAttributesUses": {
        "Types": "Kanti\\JsonToClass\\Attribute\\Types",
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    /** @var list<list<list<A___>>|string>|null */
    #[Types([[[A___::class]]], ['string'], 'null')]
    public array|null $a;
}
````
##### Kanti\GeneratedTest\Data\A___:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A___ extends AbstractJsonReadonlyClass
{
    public int $int;
}
````
