# Tested "starting with a number"
````json
[
    {
        "canBeMissing": false,
        "basicTypes": [],
        "listElement": null,
        "properties": {
            "_48x48": {
                "canBeMissing": false,
                "basicTypes": {
                    "string": true
                },
                "listElement": null,
                "properties": null
            }
        }
    }
]
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
        public A $a,
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
        public string $_48x48,
    ) {
    }
}
````
