<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Tests\Unit;

use MtsDependencyInjection\Container;
use MtsDependencyInjection\Exceptions\ContainerException;
use MtsDependencyInjection\Tests\Fakes\InstantiableWithoutParameters;
use MtsDependencyInjection\Tests\Fakes\InstantiableWithoutParametersAgain;
use MtsDependencyInjection\Tests\Fakes\InstantiableWithParameters;
use MtsDependencyInjection\Tests\Fakes\Uninstantiable;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress UnusedClass
 * @psalm-suppress MissingThrowsDocblock
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class ContainerTest extends TestCase
{
    private Container $fixture;

    /** @noinspection MethodVisibilityInspection */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Container();
    }

    /**
     * @param object|class-string|null $concrete
     *
     * @dataProvider setViewData
     */
    public function testSetView(string $abstract, string|object|null $concrete, array $expected): void
    {
        $this->fixture->set($abstract, $concrete);
        $actual = $this->fixture->view();

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<string,array<string,string|object|array<string,string|object>|null>>
     */
    public function setViewData(): iterable
    {
        $object = new stdClass();

        yield 'string concrete' => [
            'abstract' => 'abstract',
            'concrete' => 'concrete',
            'expected' => ['abstract' => 'concrete'],
        ];

        yield 'object concrete' => [
            'abstract' => 'stdClass',
            'concrete' => $object,
            'expected' => ['stdClass' => $object],
        ];

        yield 'null concrete' => [
            'abstract' => 'abstract',
            'concrete' => null,
            'expected' => ['abstract' => 'abstract'],
        ];
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function testGetClosure(): void
    {
        $abstract = 'closure';
        $concrete = static function (int $integer, string $string): string {
            return "{$integer}: {$string}";
        };
        $parameters = [
            5,
            'five',
        ];
        $expected = $concrete($parameters[0], $parameters[1]);
        $this->fixture->set($abstract, $concrete);

        /** @var string $actual */
        $actual = $this->fixture->get($abstract, $parameters);

        self::assertSame($expected, $actual);
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function testGetNotInstantiable(): void
    {
        $abstract = 'not.instantiable';
        $concrete = Uninstantiable::class;
        $this->fixture->set($abstract, $concrete);
        $message = "Class {$concrete} is not instantiable.";

        $this->expectException(ContainerException::class);
        $this->expectErrorMessage($message);

        $this->fixture->get($abstract);
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function testGetObject(): void
    {
        $abstract = 'stdClass';
        $concrete = new stdClass();
        $expected = get_class($concrete);
        $this->fixture->set($abstract, $concrete);

        /** @var object $actual */
        $actual = $this->fixture->get($abstract);

        self::assertSame($expected, get_class($actual));
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \ReflectionException
     */
    public function testGetObjectByName(): void
    {
        $abstract = InstantiableWithParameters::class;
        $integer = 5;
        $string = 'five';
        $expected = [
            'integer' => $integer,
            'string' => $string,
            'object' => InstantiableWithoutParameters::class,
            'other' => InstantiableWithoutParametersAgain::class,
        ];

        /** @var InstantiableWithParameters $object */
        $object = $this->fixture->get($abstract, [$integer, $string]);
        $actual = $object->getAll();

        self::assertSame($expected, $actual);
    }

    /**
     * @dataProvider hasData
     */
    public function testHas(string $write, string $read, bool $expected): void
    {
        $this->fixture->set($write);

        $actual = $this->fixture->has($read);

        self::assertSame($expected, $actual);
    }

    public function hasData(): iterable
    {
        yield 'valid id' => [
            'write' => 'stdClass',
            'read' => 'stdClass',
            'expected' => true,
        ];

        yield 'invalid id' => [
            'write' => 'red',
            'read' => 'blue',
            'expected' => false,
        ];
    }
}
