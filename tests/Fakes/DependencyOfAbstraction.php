<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Tests\Fakes;

/**
 * Test class
 * @psalm-suppress UnusedProperty
 */
final class DependencyOfAbstraction
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * @noinspection PhpPropertyOnlyWrittenInspection
     * @noinspection PhpPropertyCanBeReadonlyInspection
     * @phpstan-ignore-next-line
     */
    public function __construct(private FakeInterface $fakeClass)
    {
    }
}
