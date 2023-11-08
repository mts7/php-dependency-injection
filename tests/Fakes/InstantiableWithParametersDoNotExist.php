<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Tests\Fakes;

/**
 * @psalm-suppress UndefinedClass
 * @psalm-suppress UnusedProperty
 */
class InstantiableWithParametersDoNotExist
{
    /**
     * This class requires a property from a class that does not exist.
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(private readonly DoesNotExist $doesNotExist)
    {
    }
}