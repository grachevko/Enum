<?php

declare(strict_types=1);

namespace Premier\Enum\Tests;

use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Premier\Enum\Enum;
use function serialize;
use function unserialize;

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
        static::assertSame('yo', TestEnum::one()->getName());
        static::assertSame('one', AnotherTestEnum::one()->getName());
    }

    public function testGet(): void
    {
        static::assertSame('yo', TestEnum::one()->get('name'));
        static::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->get('description'));

        $this->expectException(InvalidArgumentException::class);
        TestEnum::two()->get('undefined_property');

        static::assertSame(1, TestEnum::one()->get('id'));
        static::assertSame(2, TestEnum::one()->get('id'));
    }

    public function testCustomPropertyValue(): void
    {
        static::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->getDescription());
    }

    public function testAllMethod(): void
    {
        static::assertSame([TestEnum::one(), TestEnum::two()], TestEnum::all());
        static::assertSame([TestEnum::one()], TestEnum::all([1]));
        static::assertSame([TestEnum::two()], TestEnum::all([2]));

        static::assertSame([TestEnum::one()], TestEnum::all([TestEnum::one()]));
        static::assertSame([TestEnum::two()], TestEnum::all([TestEnum::two()]));
        static::assertSame([TestEnum::two()], TestEnum::all([TestEnum::one()], true));

        static::assertSame([TestEnum::one(), TestEnum::two()], TestEnum::all(['uno', 'duo'], false, 'identifier'));
        static::assertSame([TestEnum::two()], TestEnum::all(['uno'], true, 'identifier'));
    }

    public function testReadableName(): void
    {
        $readableEnum = ReadableEnum::roleAdmin();

        static::assertSame('role_admin', $readableEnum->getName());
    }

    public function testCompositeCustomValue(): void
    {
        static::assertSame('This is two description for one', TestEnum::one()->getDescriptionTwo());
    }

    public function testMethodEq(): void
    {
        static::assertTrue(TestEnum::one()->eq(TestEnum::one()));
        static::assertFalse(TestEnum::one()->eq(TestEnum::two()));
        static::assertFalse(TestEnum::one()->eq(AnotherTestEnum::one()));
    }

    public function testSerialization(): void
    {
        $enum = TestEnum::one();

        $serialized = serialize($enum);

        $unserialized = unserialize($serialized);

        static::assertInstanceOf(TestEnum::class, $unserialized);
        static::assertTrue($enum->eq($unserialized));
    }

    public function testFrom(): void
    {
        static::assertSame('yo', TestEnum::fromName('yo')->getName());
        static::assertSame('yo', TestEnum::from('name', 'yo')->getName());

        static::assertSame(TestEnum::one(), TestEnum::from('id', 1));
    }

    public function testUndefinedMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        TestEnum::undefinedMethod();
    }
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
