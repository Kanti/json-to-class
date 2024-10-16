![Static Badge](https://img.shields.io/badge/phpstan-level:_max-blue?style=flat&logo=php)
[![codecov](https://codecov.io/gh/Kanti/json-to-class/graph/badge.svg?token=RN6OGgDK19)](https://codecov.io/gh/Kanti/json-to-class)
![Packagist Downloads](https://img.shields.io/packagist/dm/kanti/json-to-class)
![Packagist Version](https://img.shields.io/packagist/v/kanti/json-to-class)

# json to class (generates PHP)

automatically generated PHP Classes from JSON or other data(database Rows, CSV, etc.)

## Installation

````bash
compsoer require kanti/json-to-class
````

## Usage

````php
$person = \Kanti\JsonToClass\Converter\Converter::getInstance()
    ->convert(\MyCode\Person::class, ['name' => 'Kanti', 'age' => 30]);
assert($person instanceof \MyCode\Person);
assert($person->name === 'Kanti');
assert($person->age === 30);
````
This will generate a Class just like this.

````php
<?php

declare(strict_types=1);

namespace MyCode;

use Kanti\JsonToClass\Attribute\RootClass;

#[RootClass]
final readonly class Person {
    public string $name;
    public int $age;
}
````

## Performance

Tests performed:
- on a `ThinkPad` `Intel(R) Core(TM) i7-8665U CPU @ 1.90GHz   2.11 GHz`
- inside `WSL` with a `Docker Container` with `PHP 8.2.23`
- the env `JSON_TO_CLASS_CREATE=no` was set.  
- the class structure is a mix of complex and simple classes, with a total of `289` classes.
  - some have 2-3 fields
  - a few have ~115 fields.

### Results:

total classes Created: `155_078`  
total time: `3.0614130496979s`  
time per class: `19.741117693663µs`  
> I could create `~50_655` classes per second, so you should be fine with the performance.

## Features
- [x] generate PHP Classes from data structures (JSON, CSV, DB, etc.)
- [ ] combine similar classes (e.g. `Person` and `Employee` with the same fields)
- [ ] generate PHP Classes from JSON Schema
- [ ] generate JSON Schema from data structures (JSON, CSV, DB, etc.)

## TODOs
- if type is only `null` or `array{}` or `stdClass{}` then it we remove the property
- default wenn zu viele Felder kommen: Warning im Logging (PSR Logger / Sentry Logger)
- add Styker: https://infection.github.io/guide/mutation-badge.html#How-to-set-it-up
- add PSR Event system to make it possible to add custom logic `BeforeClassMapped`/`AfterClassMapped`, `BeforeClassWritten`/`AfterClassWritten` etc.
- decide if we should use reflection or `::from` methods
- maybe add log of schemas of all data transformed (commutative schema log)
- on warning and error: log with help to change the schema/Classes so the current data is working with the Classes
- will have a command `vendor/bin/json-to-class add-schema <data>` that will help to add schema to the class (from log message)
- will have a command `vendor/bin/json-to-class check-attributes` (returns non zero exit code if something needs to change)
  will have a command `vendor/bin/json-to-class from-attributes` (dose the needed changes, if there are some)
`````php
#[FromData('./data/person.json')]
#[FromData('https://example.com/importantPersons.json')]
class Person {
}

#[FromSchema('./schema/person.json')]
#[FromSchema('https://json-schema.org/draft/2020-12/schema')]
class Person {
}
`````


Idea: do not generate property content:
````php
#[Ignore('content')]
class Person {
}
````

Idea: do not create classes for complex types, just use the type hint
````php
#[KeepData('content')]
class Person {
    /**
     * @param {...} $content
     * @phpstan-type ....
     */
    public function __construct(
        public mixed $content,
    ) {}
}
````
