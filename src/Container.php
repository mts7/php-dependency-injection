<?php

declare(strict_types=1);

namespace MtsDependencyInjection;

use Closure;
use MtsDependencyInjection\Exceptions\ContainerException;
use MtsDependencyInjection\Exceptions\MissingContainerDefinitionException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use TypeError;

/**
 * @see https://medium.com/tech-tajawal/dependency-injection-di-container-in-php-a7e5d309ccc6
 */
class Container implements ContainerInterface
{
    /**
     * This is used in a validation of the dependency provided.
     */
    public const INVALID_DEPENDENCY = 'invalid dependency';

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
        if (!$this->has($id)) {
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
     * @param array<string|int,class-string|object|null> $config
     */
    public function load(array $config): void
    {
        foreach ($config as $abstract => $concrete) {
            $prepared = $this->prepareAbstraction($concrete);
            if ($prepared === self::INVALID_DEPENDENCY) {
                continue;
            }
            $this->set(is_int($abstract) ? $prepared : $abstract, $concrete);
        }
    }

    public function view(): array
    {
        return $this->instances;
    }

    /**
     * @param object|class-string|null $concrete
     */
    private function prepareAbstraction(object|string|null $concrete): string
    {
        if ($concrete === null) {
            return self::INVALID_DEPENDENCY;
        }

        if (is_string($concrete)) {
            if (class_exists($concrete)) {
                return $concrete;
            }

            return self::INVALID_DEPENDENCY;
        }

        return $concrete::class;
    }

    /**
     * @param class-string|object $concrete
     *
     * @return object|mixed|null
     *
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     */
    private function resolve(object|string $concrete, array $parameters): mixed
    {
        if ($concrete instanceof Closure) {
            return call_user_func_array($concrete, $parameters);
        }

        try {
            if (empty($parameters) && !empty($concrete::class)) {
                // get the instantiated object as a default
                return $concrete;
            }
            // @phpstan-ignore-next-line
        } catch (TypeError) {
        }

        return $this->autoWire($concrete, $parameters);
    }

    /**
     * @param class-string|object $concrete
     *
     * @return mixed|object|null
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     *
     * @psalm-suppress PossiblyInvalidCast
     */
    private function autoWire(object|string $concrete, array $parameters): mixed
    {
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
            if (array_key_exists($index, $parameters)) {
                $dependencies[] = $parameters[$index];
                continue;
            }
            /** @var \ReflectionNamedType $dependency */
            $dependency = $parameter->getType();
            $dependencyType = $dependency->getName();
            // get a new object for the dependency instead of checking for an existing dependency in $parameters
            if (class_exists($dependencyType)) {
                $dependencies[] = $this->get($dependencyType);
            }
        }

        return $dependencies;
    }
}
