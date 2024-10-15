# Tested "expectedUses"
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
            "classSchema": {
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
            }
        }
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array",
    "expectedDocBlockType": "list<Data_>|Data",
    "expectedAttribute": {},
    "expectedUses": {
        "Data": "Kanti\\GeneratedTest\\Data",
        "Data_": "Kanti\\GeneratedTest\\Data_"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\GeneratedTest\Data\A_;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;

#[RootClass]
final readonly class Data
{
    /** @var list<A_>|A */
    #[Types([A_::class], A::class)]
    public A|array $a;
}
````
##### Kanti\GeneratedTest\Data\A:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\GeneratedTest\Data\A\ClassSchema;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A
{
    public ClassSchema $classSchema;
}
````
##### Kanti\GeneratedTest\Data\A_:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class A_
{
    public int $int;
}
````
##### Kanti\GeneratedTest\Data\A\ClassSchema:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data\A;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass(Data::class)]
final readonly class ClassSchema
{
    public int $int;
}
````
