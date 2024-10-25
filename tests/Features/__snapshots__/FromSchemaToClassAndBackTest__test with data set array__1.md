# Tested "array{}"
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
        "dataKeys": null
    },
    "expectedPhpType": "array",
    "expectedDocBlockType": "array{}",
    "expectedAttributes": [
        {}
    ],
    "expectedAttributesUses": {
        "Types": "Kanti\\JsonToClass\\Attribute\\Types"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    /** @var array{} */
    #[Types([])]
    public array $a;
}
````
