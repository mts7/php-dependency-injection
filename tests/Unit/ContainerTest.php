<?php

declare(strict_types=1);

namespace MtsDependencyInjection\Tests\Unit;

use MtsDependencyInjection\Container;
use MtsDependencyInjection\Exceptions\ContainerException;
use MtsDependencyInjection\Exceptions\MissingContainerDefinitionException;
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
    public function testGetNoInstance(): void
    {
        $id = InstantiableWithoutParameters::class;
        $message = "Create a definition by using `\$container->set('{$id}');` prior to getting the object.";

        $this->expectException(MissingContainerDefinitionException::class);
        $this->expectErrorMessage($message);

        $this->fixture->get($id);
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
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
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
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
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
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
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
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
        $this->fixture->set($abstract);
        $this->fixture->set(InstantiableWithoutParameters::class);
        $this->fixture->set(InstantiableWithoutParametersAgain::class);

        /** @var InstantiableWithParameters $object */
        $object = $this->fixture->get($abstract, [$integer, $string]);
        $actual = $object->getAll();

        self::assertSame($expected, $actual);
    }

    /**
     * @throws \MtsDependencyInjection\Exceptions\ContainerException
     * @throws \MtsDependencyInjection\Exceptions\MissingContainerDefinitionException
     * @throws \ReflectionException
     */
    public function testGetObjectByAlias(): void
    {
        $abstract = 'alias';
        $integer = 5;
        $string = 'five';
        $expected = [
            'integer' => $integer,
            'string' => $string,
            'object' => InstantiableWithoutParameters::class,
            'other' => InstantiableWithoutParametersAgain::class,
        ];
        $this->fixture->set($abstract, InstantiableWithParameters::class);
        $this->fixture->set(InstantiableWithoutParameters::class);
        $this->fixture->set(InstantiableWithoutParametersAgain::class);

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

    /**
     * @return iterable<string,array<string,string|bool>>
     */
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

    /**
     * @param array<string,class-string|object|null> $config
     * @param array<string,bool> $expected
     *
     * @dataProvider loadData
     */
    public function testLoad(array $config, array $expected): void
    {
        $this->fixture->load($config);

        foreach ($expected as $key => $value) {
            self::assertSame($value, $this->fixture->has($key));
        }
    }

    /**
     * @return iterable<string,array<string,array>>
     */
    public function loadData(): iterable
    {
        yield 'various concrete types' => [
            'config' => [
                'null' => null,
                'string' => 'string',
                'closure' => static function () {
                },
                'object' => new stdClass(),
                'class' => InstantiableWithoutParameters::class,
            ],
            'expected' => [
                'null' => true,
                'non-existent' => false,
                'string' => true,
                'closure' => true,
                'object' => true,
                'class' => true,
            ],
        ];
    }
}
