# json to class

automatically generated PHP Classes from JSON or other data(database Rows, CSV, etc.)

## Installation

````bash
compsoer require kanti/json-to-class
````

## Usage

````php
$person = (new \Kanti\JsonToClass\DevelopmentConverter())
    ->convert(\MyCode\Person::class, ['name' => 'Kanti', 'age' => 30]);
assert($person instanceof \MyCode\Person);
assert($person->name === 'Kanti');
assert($person->age === 30);
````
This will generate a Class just like this.

````php
<?php

/**
 * declare(strict_types=1); missing by design
 * This file is generated by kanti/json-to-class
 */

namespace MyCode;

use Kanti\JsonToClass\Transformer\Transformer;

class Person
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function from(array $data, Transformer $transformer): self
    {
        return new self(
            ...$transformer
            ->for($data)
            ->native('name')
            ->native('age')
        );
    }
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
- default wenn zu viele Felder kommen: Warning im Logging (PSR Logger / Sentry Logger)
- decide if we should use reflection or `::from` methods
- monkey patching of classes (not possible right now :/)
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
