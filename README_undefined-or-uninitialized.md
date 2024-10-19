# json to class: undefined properties  (json_encode):

currently we have the Problem that we can not clearly differentiate between null and not defined properties.

For this reason we use the uninitialized properties as a way to represent `undefined` properties.
For this reason we can not use promoted properties in the constructor, because they are always initialized.

with this it is possible to have a json like this:
````php
class Z
{
    public ?string $uninitializedProp
}
````
````json
{}
````

and getting the var_dump like this:
````php
object(Z)#1 (0) {
  ["uninitializedProp"]=>
  uninitialized(?string)
}
````

and the json like this:
````json
{"uninitializedProp":null}
````

will result in a var_dump like this:
````php
object(Z)#1 (1) {
  ["uninitializedProp"]=>
  NULL
}
````

but now we can not use this in combination with canBeEmpty.
if the would use the property we always would need to use the ?? operator to get the value.

````php
$z->uninitialized ?? null;
````

otherwise the risk is that we get a `uninitialized` error.

with php 8.4 there is a way to get null in php code but for json_encode get the `uninitialized` value.

````php
class Z implements \JsonSerializable
{
    public ?string $uninitializedProp {
     get => $this->uninitializedProp ?? null;
     set => $value;
    }

    public function jsonSerialize(): stdClass
    {
        // (array) ignores property hooks 
        return (object)(array)$this;
    }
}
````

with this setup something like this will work:

```php
$json='[{}, {"uninitializedProp":null}, {"uninitializedProp":"A"}]';
$instances = \Kanti\JsonToClass\Converter\Converter::getInstance()
    ->jsonDecodeList(\MyCode\Z::class, $json);
assert(3 === count($json));
assert($instances[0] instanceof \MyCode\Z);
assert($instances[0]->uninitializedProp === null);
assert($instances[1] instanceof \MyCode\Z);
assert($instances[1]->uninitializedProp === null);
assert($instances[2] instanceof \MyCode\Z);
assert($instances[2]->uninitializedProp === 'A');
assert($json === json_encode($instances)); // there the first object in json is still undefined
```

but we don't have PHP 8.4 right now :/ maybe we find another way to solve this problem?

currently we could use `null` instead of not initializing the property, but this would make it impossible to differentiate between `null` and `undefined`. so the json_encode at the end would not be correct.
or we could keep the `uninitialized` value, but this would make it impossible to use the property without the `??` operator. And the code would be more complex.



# V2 solution with PHP version lower than 8.4

````php
trait MuteUninitializedPropertyError
{
    /**
     * this will only work if the property was uninitialized and additionally unset
     */
    public function __get(string $name): mixed
    {
        // property_exists is even true if the property was unset
        if (property_exists($this, $name)) {
            if ((new ReflectionProperty($this, $name))->getType()?->allowsNull()) {
                return $this->{$name} ?? null; // mute error Typed property %s::$%s must not be accessed before initialization
            }

            return $this->{$name}; // throws error Typed property %s::$%s must not be accessed before initialization
        }

        return $this->{$name}; // triggers Warning: Undefined property: %s::$%s in %s on line %d
    }
}

final readonly class Z
{
    use MuteUninitializedPropertyError;

    public ?string $uninitializedProp;
}

$z = new Z();
\Kanti\JsonToClass\unsetFromObject($z, 'uninitializedProp');
assert($z->uninitializedProp === null);
````
