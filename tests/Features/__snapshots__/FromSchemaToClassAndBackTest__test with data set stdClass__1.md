# Tested "stdClass{}"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": null,
        "dataKeys": []
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data",
    "expectedUses": {
        "Data": "Kanti\\GeneratedTest\\Data"
    }
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass]
final readonly class Data extends AbstractJsonReadonlyClass
{
    public A $a;
}
````
##### Kanti\GeneratedTest\Data\A:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest\Data;

use Kanti\GeneratedTest\Data;
use Kanti\JsonToClass\Attribute\RootClass;
use Kanti\JsonToClass\Dto\AbstractJsonReadonlyClass;

#[RootClass(Data::class)]
final readonly class A extends AbstractJsonReadonlyClass
{
}
````
