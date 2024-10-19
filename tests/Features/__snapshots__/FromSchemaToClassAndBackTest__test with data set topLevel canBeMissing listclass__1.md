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
    "expectedPhpType": "array|null",
    "expectedDocBlockType": "list<Data_>|null",
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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass]
final readonly class Data
{
    use MuteUninitializedPropertyError;

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
use Kanti\JsonToClass\Dto\MuteUninitializedPropertyError;

#[RootClass(Data::class)]
final readonly class A_
{
    use MuteUninitializedPropertyError;

    public int $int;
}
````
