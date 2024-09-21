# Tested "topLevel canBeMissing Data"
````json
{
    "schema": {
        "canBeMissing": true,
        "basicTypes": {
            "null": true
        },
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
    "expectedPhpType": "Kanti\\GeneratedTest\\Data|null"
}
````
##### Kanti\GeneratedTest\Data:
````php
<?php

declare(strict_types=1);

namespace Kanti\GeneratedTest;

use Kanti\GeneratedTest\Data\A;
use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass]
final readonly class Data
{
    public function __construct(
        public A|null $a = null,
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
    public function __construct(
        public int $int,
    ) {
    }
}
````
