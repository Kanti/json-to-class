# Tested "stdClass{}"
````json
{
    "schema": {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": null,
        "properties": []
    },
    "expectedPhpType": "Kanti\\GeneratedTest\\Data"
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
use Kanti\JsonToClass\v2\Attribute\RootClass;

#[RootClass]
final readonly class Data
{
    public function __construct(
        public A $a,
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
    public function __construct()
    {
    }
}
````