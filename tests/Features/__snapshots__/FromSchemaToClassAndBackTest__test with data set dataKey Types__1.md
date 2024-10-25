# Tested "dataKey Types"
````json
{
    "dataKey": "class🌏Schema",
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": {
            "canBeMissing": false,
            "basicTypes": [],
            "listElement": null,
            "dataKeys": {
                "int": {
                    "canBeMissing": false,
                    "basicTypes": {
                        "int": true
                    },
                    "listElement": null,
                    "dataKeys": null
                }
            }
        },
        "dataKeys": {
            "classSchema": {
                "canBeMissing": false,
                "basicTypes": [],
                "listElement": null,
                "dataKeys": {
                    "int": {
                        "canBeMissing": false,
                        "basicTypes": {
                            "int": true
                        },
                        "listElement": null,
                        "dataKeys": null
                    }
                }
            }
        }
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|array",
    "expectedPhpTypeUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    },
    "expectedDocBlockType": "list<Data_>|Data",
    "expectedDocBlockUses": {
        "Data": "Kanti\\GeneratedTest\\Data",
        "Data_": "Kanti\\GeneratedTest\\Data_"
    },
    "expectedAttributes": [
        {},
        {}
    ],
    "expectedAttributesUses": {
        "Key": "Kanti\\JsonToClass\\Attribute\\Key",
        "Types": "Kanti\\JsonToClass\\Attribute\\Types",
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
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Attribute\Types;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    /** @var list<A_>|A */
    #[Key('class🌏Schema')]
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A extends AbstractJsonReadonlyClass
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A_ extends AbstractJsonReadonlyClass
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
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class ClassSchema extends AbstractJsonReadonlyClass
{
    public int $int;
}
````
