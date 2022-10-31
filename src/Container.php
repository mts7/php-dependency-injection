<?php

declare(strict_types=1);

namespace MtsDependencyInjection;

use Closure;
use MtsDependencyInjection\Exceptions\ContainerException;
use MtsDependencyInjection\Exceptions\MissingContainerDefinitionException;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @see https://medium.com/tech-tajawal/dependency-injection-di-container-in-php-a7e5d309ccc6
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string,class-string|object> $instances
     */
    private array $instances = [];

    /**
     * @param class-string|string $abstract
     * @param class-string|object|null $concrete
     */
    public function set(string $abstract, string|object|null $concrete = null): void
    {
        if ($concrete === null) {
            /**
             * @var class-string $concrete
             * @noinspection PhpRedundantVariableDocTypeInspection
             */
            $concrete = $abstract;
        }
        $this->instances[$abstract] = $concrete;
    }

    /**
     * @return mixed|object|null
     *
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     */
    public function get(string $id, array $parameters = []): mixed
    {
        if (!isset($this->instances[$id])) {
            throw new MissingContainerDefinitionException(
                "Create a definition by using `\$container->set('{$id}');` prior to getting the object."
            );
        }

        return $this->resolve($this->instances[$id], $parameters);
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * @param array<string,class-string|object|null> $config
     */
    public function load(array $config): void
    {
        foreach ($config as $abstract => $concrete) {
            $this->set($abstract, $concrete);
        }
    }

    public function view(): array
    {
        return $this->instances;
    }

    /**
     * @param class-string|object $concrete
     *
     * @return object|mixed|null
     *
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     *
     * @psalm-suppress PossiblyInvalidCast
     */
    private function resolve(object|string $concrete, array $parameters): mixed
    {
        if ($concrete instanceof Closure) {
            return call_user_func_array($concrete, $parameters);
        }

        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if ($constructor === null) {
            return $reflector->newInstance();
        }

        $dependencies = $this->buildParameters($parameters, $constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * @param \ReflectionParameter[] $reflectionParameters
     *
     * @return array<int,mixed>
     *
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     *
     * @psalm-suppress MixedAssignment
     */
    private function buildParameters(array $parameters, array $reflectionParameters): array
    {
        $dependencies = [];
        foreach ($reflectionParameters as $index => $parameter) {
            /** @var \ReflectionNamedType $dependency */
            $dependency = $parameter->getType();
            $dependencyType = $dependency->getName();
            // get a new object for the dependency instead of checking for an existing dependency in $parameters
            if (class_exists($dependencyType)) {
                $dependencies[] = $this->get($dependencyType);
                continue;
            }
            // if the current constructor parameter is not a class, use the provided parameter
            $dependencies[] = $parameters[$index];
        }

        return $dependencies;
    }
}
