<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Tests\Fakes;

/**
 * Test class
 */
final class InstantiableWithParameters
{
    public function __construct(
        private readonly int $integer,
        private readonly string $string,
        private readonly InstantiableWithoutParameters $object,
        private readonly InstantiableWithoutParametersAgain $other,
    ) {
    }

    public function getAll(): array
    {
        return [
            'integer' => $this->integer,
            'string' => $this->string,
            'object' => $this->object::class,
            'other' => $this->other::class,
        ];
    }
}
