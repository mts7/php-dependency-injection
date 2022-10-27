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

class Color
{
    private string $hex = '';

    public function setHex(string $hex): void
    {
        $this->hex = $hex;
    }

    public function getHex(): string
    {
        return $this->hex;
    }
}

class Car
{
    public function __construct(private string $name, private readonly Color $color)
    {
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

$container = new Container();
$container->set(Color::class);
$container->set(Car::class);

try {
    // there is no need to pass `Color` to the container since all dependencies load automatically
    /** @var Car $car */
    $car = $container->get(Car::class, ['Alice']);
    $car->getColor()->setHex('#F0F8FF');
    echo $car->getName() . ' is ' . $car->getColor()->getHex() . PHP_EOL;
} catch (ContainerException|MissingContainerDefinitionException|ReflectionException $exception) {
    echo $exception->getMessage();
}
