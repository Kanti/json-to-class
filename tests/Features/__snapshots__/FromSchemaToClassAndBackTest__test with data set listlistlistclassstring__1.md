# Tested "list<list<list<class>>|string>"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
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
    "expectedDocBlockType": "list<list<list<Data___>>|string>",
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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass]
final readonly class Data
{
    use MuteUninitializedPropertyError;

    /** @var list<list<list<A___>>|string> */
    #[Types([[[A___::class]]], ['string'])]
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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass(Data::class)]
final readonly class A___
{
    use MuteUninitializedPropertyError;

    public int $int;
}
````
