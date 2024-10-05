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
            "properties": null
        },
        "properties": null
    },
    "expectedPhpType": "array",
    "expectedDocBlockType": "array{}",
    "expectedAttribute": {}
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /**
     * @param array{} $a
     */
    public function __construct(
        #[Types([])]
        public array $a,
    ) {
    }
}
````
