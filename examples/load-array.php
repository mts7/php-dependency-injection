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
    private string $name = '';

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

/**
 * The factory creates a container with the provided config array. Since the
 * container only cares about having a valid array, the factory could be used to
 * use an array from a config file, class, or array that it knows about or
 * receives. One such implementation (not illustrated here) would be to store
 * the configuration in a YAML file, have a YAML file parser parse the config into
 * an array, and then use that array to send to Container. Each project using
 * this library should create its own ContainerFactory and related files.
 */
class ContainerFactory
{
    /**
     * @param array<string,class-string|object|null> $config
     */
    public static function create(array $config = []): Container
    {
        $container = new Container();
        $container->load($config);

        return $container;
    }
}

$config = [
    Car::class => new Car(),
];
$container = ContainerFactory::create($config);

try {
    /** @var Car $car */
    $car = $container->get(Car::class);
    $car->setName('Taco');
    echo $car->getName() . PHP_EOL;
} catch (ContainerException|MissingContainerDefinitionException|ReflectionException $exception) {
    echo $exception->getMessage() . PHP_EOL;
}
