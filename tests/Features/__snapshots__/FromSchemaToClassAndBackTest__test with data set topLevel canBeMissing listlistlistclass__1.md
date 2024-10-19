# Tested "topLevel canBeMissing list<list<list<class>>>"
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
    "expectedPhpType": "array|null",
    "expectedDocBlockType": "list<list<list<Data___>>>|null",
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

    /** @var list<list<list<A___>>>|null */
    #[Types([[[A___::class]]], 'null')]
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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass(Data::class)]
final readonly class A___
{
    use MuteUninitializedPropertyError;

    public int $int;
}
````
