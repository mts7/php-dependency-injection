# PHP Dependency Injection

PHP Dependency Injection Container

## Usage

Getting an instantiated object can only happen after the container knows about
the class. The only way to tell the container about the definition is to use the
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

When a class has its own dependencies, all dependencies can exist once they are
set in the Container. The container uses autowiring through Reflection to
determine which parameters are classes and then instantiate each one.

[Full Example](examples/get-autowiring.php)

```php
$container = new Container();
$container->set(Color::class);
$container->set(Car::class);

// there is no need to pass `Color` to the container since all dependencies load automatically
$car = $container->get(Car::class, ['Alice']);
// getting the color comes from Car's dependency rather than an outside influence
$car->getColor()->setHex('#F0F8FF');
echo $car->getName() . ' is ' . $car->getColor()->getHex();
```
