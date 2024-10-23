# Tested "dataKey"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": null,
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
    "expectedPhpType": "Kanti\\GeneratedTest\\Data",
    "expectedAttributes": [
        {}
    ],
    "dataKey": "class-Schema",
    "expectedUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    },
    "expectedUsesAttributes": {
        "Key": "Kanti\\JsonToClass\\Attribute\\Key"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\JsonToClass\Attribute\Key;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    #[Key('class-Schema')]
    public A $a;
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
