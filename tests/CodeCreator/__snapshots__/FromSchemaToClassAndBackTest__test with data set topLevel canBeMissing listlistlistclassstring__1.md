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
    "expectedDocBlockType": "list<list<list<Data___>>|string>|null",
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
    /**
     * @param list<list<list<A___>>|string>|null $a
     */
    public function __construct(
        #[Types([[[A___::class]]], ['string'], 'null')]
        public array|null $a = null,
    ) {
    }
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
    public function __construct(
        public int $int,
    ) {
    }
}
````
