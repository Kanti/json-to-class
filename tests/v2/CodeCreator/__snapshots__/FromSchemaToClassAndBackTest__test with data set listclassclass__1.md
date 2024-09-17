# Tested "list<class>|class"
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
        "properties": {
            "empty": {
                "canBeMissing": true,
                "basicTypes": {
                    "null": true
                },
                "listElement": null,
                "properties": null
            }
        }
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array",
    "expectedDocBlockType": "list<L>|Data",
    "expectedAttribute": {},
    "expectedUses": {
        "L": "Kanti\\GeneratedTest\\Data\\L"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

/**
 * declare(strict_types=1); missing by design
 * This file is generated by kanti/json-to-class
 */

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\GeneratedTest\Data\A\L;
use Kanti\JsonToClass\v2\Attribute\RootClass;
use Kanti\JsonToClass\v2\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /**
     * @param list<L>|A $a
     */
    public function __construct(
        #[Types([L::class], A::class)]
        public A|array $a,
    ) {
    }
}
````
##### Kanti\GeneratedTest\Data\A:
````php
<?php

/**
 * declare(strict_types=1); missing by design
 * This file is generated by kanti/json-to-class
 */

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\v2\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A
{
    public function __construct(
        public null $empty = null,
    ) {
    }
}
````
##### Kanti\GeneratedTest\Data\A\L:
````php
<?php

/**
 * declare(strict_types=1); missing by design
 * This file is generated by kanti/json-to-class
 */

namespace Kanti\GeneratedTest\Data\A;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\v2\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class L
{
    public function __construct(
        public int $int,
    ) {
    }
}
````