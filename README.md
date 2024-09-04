# json to class

automatically generated PHP Classes from JSON or other data(database Rows, CSV, etc.)

## Installation

````bash
compsoer require kanti/json-to-class
````

## Usage

````php
$person = \Kanti\JsonToClass\DevelopmentConverter::createInstance(true)->convert(\MyCode\Person::class, ['name' => 'Kanti', 'age' => 30]);
assert($person instanceof \MyCode\Person);
assert($person->name === 'Kanti');
assert($person->age === 30);
````
This will generate a Class just like this.

````php
<?php

namespace MyCode;

class Person
{
    public function __construct(
        public string $name,
        public int $age,
    ) {
    }
}
````

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

# ????
#[Transform(onMissingProperties: ErrorType::warning, onExtraProperties: ErrorType::warning)]
class Person {
}
`````
