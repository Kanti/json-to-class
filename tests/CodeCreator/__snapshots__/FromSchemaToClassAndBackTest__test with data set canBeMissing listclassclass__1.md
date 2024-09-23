# Tested "canBeMissing list<class>|class"
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
        "properties": []
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

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\GeneratedTest\Data\A\L;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

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

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A
{
    public function __construct()
    {
    }
}
````
##### Kanti\GeneratedTest\Data\A\L:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data\A;

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