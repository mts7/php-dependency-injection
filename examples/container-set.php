<?php

declare(strict_types=1);

use MtsDependencyInjection\Container;
use MtsDependencyInjection\Exceptions\ContainerException;
use MtsDependencyInjection\Exceptions\MissingContainerDefinitionException;

/**
 * @psalm-suppress UnusedPsalmSuppress
 * @psalm-suppress MissingFile
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

class Car
{
}

$container = new Container();

try {
    // set an alias with a reference to the class name
    $container->set('car', Car::class);
    /** @var Car $car */
    $car = $container->get('car');
    echo $car::class . PHP_EOL;

    // set the name to the class name and provide an instantiated object
    $container->set(Car::class, new Car());
    /** @var Car $car */
    $car = $container->get(Car::class);
    echo $car::class . PHP_EOL;

    // provide only the name of the class and get an instantiated object
    $container->set(Car::class);
    /** @var Car $car */
    $car = $container->get(Car::class);
    echo $car::class . PHP_EOL;
} catch (ContainerException|MissingContainerDefinitionException|ReflectionException $exception) {
    echo $exception->getMessage();
}
