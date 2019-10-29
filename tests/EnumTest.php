<?php

namespace Premier\Enum\Tests;

use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Premier\Enum\Enum;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumTest extends TestCase
{
    public function testEmptyEnum(): void
    {
        $this->expectException(LogicException::class);

        EmptyEnum::create(1);
    }

    public function testIntegerValue(): void
    {
        $this->expectException(LogicException::class);

        StringValueEnum::create(1);
    }

    public function testNameValue(): void
    {
        self::assertSame('yo', TestEnum::one()->getName());
        self::assertSame('one', AnotherTestEnum::one()->getName());
    }

    public function testGet(): void
    {
        self::assertSame('yo', TestEnum::one()->get('name'));
        self::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->get('description'));

        $this->expectException(InvalidArgumentException::class);
        TestEnum::two()->get('undefined_property');
    }

    public function testCustomPropertyValue(): void
    {
        self::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->getDescription());
    }

    public function testAllMethod(): void
    {
        self::assertSame([TestEnum::one(), TestEnum::two()], TestEnum::all());
        self::assertSame([TestEnum::one()], TestEnum::all([1]));
        self::assertSame([TestEnum::two()], TestEnum::all([2]));
        self::assertSame([TestEnum::one()], TestEnum::all(['1']));
        self::assertSame([TestEnum::two()], TestEnum::all(['2']));
        self::assertSame([TestEnum::one()], TestEnum::all([TestEnum::one()]));
        self::assertSame([TestEnum::two()], TestEnum::all([TestEnum::two()]));
    }

    public function testReadableName(): void
    {
        $readableEnum = ReadableEnum::roleAdmin();

        self::assertSame('role_admin', $readableEnum->getName());
    }

    public function testCompositeCustomValue(): void
    {
        self::assertSame('This is two description for one', TestEnum::one()->getDescriptionTwo());
    }

    public function testMethodEq(): void
    {
        self::assertTrue(TestEnum::one()->eq(TestEnum::one()));
        self::assertFalse(TestEnum::one()->eq(TestEnum::two()));
        self::assertFalse(TestEnum::one()->eq(AnotherTestEnum::one()));
    }

    public function testSerialization(): void
    {
        $enum = TestEnum::one();

        $serialized = serialize($enum);

        $unserialized = unserialize($serialized);

        self::assertInstanceOf(TestEnum::class, $unserialized);
        self::assertTrue($enum->eq($unserialized));
    }

    public function testFrom(): void
    {
        self::assertSame('yo', TestEnum::fromName('yo')->getName());
        self::assertSame('yo', TestEnum::from('name', 'yo')->getName());
    }

    public function testUndefinedMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        TestEnum::undefinedMethod();
    }
}

/**
 * @method static TestEnum one()
 * @method static TestEnum two()
 * @method string getDescription()
 * @method string getDescriptionTwo()
 * @method static self fromName(string $name)
 * @method static self undefinedMethod()
 */
class TestEnum extends Enum
{
    private const ONE = 1;
    private const TWO = 2;

    /**
     * @var array
     */
    private static $name = [
        self::ONE => 'yo',
        self::TWO => 'Double yo',
    ];

    /**
     * @var array
     */
    private static $description = [
        self::ONE => 'This is a description for TestEnum::ONE',
        self::TWO => 'This is a description for TestEnum::TWO',
    ];

    /**
     * @var array
     */
    private static $descriptionTwo = [
        self::ONE => 'This is two description for one',
        self::TWO => 'This is two description for two',
    ];
}

/**
 * @method static AnotherTestEnum one()
 */
class AnotherTestEnum extends Enum
{
    private const ONE = 1;
}

class EmptyEnum extends Enum
{
}

class StringValueEnum extends Enum
{
    private const ONE = 1;
    private const WRONG_VALUE = 'string';
}

/**
 * @method static ReadableEnum roleAdmin()
 */
class ReadableEnum extends Enum
{
    private const ROLE_ADMIN = 1;
}

/**
 * @method static PrivateConstEnum iAmPrivate()
 * @method bool   isIAmPrivate()
 */
class PrivateConstEnum extends Enum
{
    private const I_AM_PRIVATE = 1;
}
