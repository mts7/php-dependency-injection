# PHP Dependency Injection

PHP Dependency Injection Container

## Installation

```shell
composer require mts7/php-dependency-injection
```

## Usage

### Set

Getting an instantiated object can only happen after the container knows about
the class. One way to tell the container about the definition is to use the
`set` method. `set` can take an alias or fully-qualified class name as the first
parameter and a fully-qualified class name, instantiated object, or null as the
second parameter.

[Full Example](examples/container-set.php)

```php
$container = new Container();

// set an alias with a reference to the class name
$container->set('car', Car::class);
$car = $container->get('car');

// set the name to the class name and provide an instantiated object
$container->set(Car::class, new Car());
$car = $container->get(Car::class);

// provide only the name of the class and get an instantiated object
$container->set(Car::class);
$car = $container->get(Car::class);
```

### Load

To make things easier for projects, passing the abstract and concrete values to
Container can happen through the `load` method. Provide an array indexed by the
abstract ID (key, class name, etc.) with a value of a concretion.

Load handles a variety of parameters, allowing for most implementations to work
out-of-the-box. When both the key and value are present, the list of definitions
available adds valid values and their keys. When the config array has no key,
`load` uses the value to determine the key (which should be the fully-qualified
class name). Each value goes through validation to determine if the concretion
provided is a valid class or object. Check the 
[tests](tests/Unit/ContainerTest.php#LC325) for examples.

Load is best used when combined with a factory that provides auto-loading of a
preconfigured list of abstractions and their concretions.

[Full Example](examples/load-array.php)

```php
// the factory would create a new Container, then call ->load($config) with the appropriate values
$container = ContainerFactory::create();

$car = $container->get(Car::class);
$car->setName('Taco');
echo $car->getName();
```

### Get

When a class has its own dependencies, all dependencies can exist once they are
set in the container. The container uses auto-wiring through Reflection to
determine which parameters are classes and then instantiate each one.

[Full Example](examples/get-autowiring.php)

```php
$container = new Container();
$container->load([Color::class, Car::class]);

// there is no need to pass `Color` to the container since all dependencies load automatically
$car = $container->get(Car::class, ['Alice']);
// getting the color comes from Car's dependency rather than an outside influence
$car->getColor()->setHex('#F0F8FF');
echo $car->getName() . ' is ' . $car->getColor()->getHex();
```
