# Tested "canBeMissing list<list<list<class>>|string>"
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
    "expectedDocBlockType": "list<list<list<L>>|string>",
    "expectedAttribute": {},
    "expectedUses": {
        "L": "Kanti\\GeneratedTest\\Data\\L\\L\\L"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A\L\L\L;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /**
     * @param list<list<list<L>>|string> $a
     */
    public function __construct(
        #[Types([[[L::class]]], ['string'])]
        public array $a,
    ) {
    }
}
````
##### Kanti\GeneratedTest\Data\A\L\L\L:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data\A\L\L;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class L
{
    public function __construct(
        public int $int,
    ) {
    }
}
````
