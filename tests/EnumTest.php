<?php

namespace Grachevko\Enum\Tests;

use Grachevko\Enum\Enum;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumTest extends TestCase
{
    public function testEmptyEnum(): void
    {
        $this->expectException(LogicException::class);

        new EmptyEnum(1);
    }

    public function testInstantiation(): void
    {
        self::assertInstanceOf(Enum::class, TestEnum::one());
    }

    public function testIntegerValue(): void
    {
        $this->expectException(LogicException::class);

        new StringValueEnum(1);
    }

    public function testNameValue(): void
    {
        self::assertSame('yo', TestEnum::one()->getName());
        self::assertSame('two', TestEnum::two()->getName());
        self::assertSame('Two', TestEnum::two()->getReadableName());
    }

    public function testCustomPropertyValue(): void
    {
        self::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->getDescription());
    }

    public function testCustomUndefinedValue(): void
    {
        $this->expectException(LogicException::class);

        TestEnum::one()->getDescription();
    }

    public function testAllMethod(): void
    {
        self::assertEquals([TestEnum::one(), TestEnum::two()], TestEnum::all());
        self::assertEquals([TestEnum::one()], TestEnum::all([TestEnum::ONE]));
        self::assertEquals([TestEnum::two()], TestEnum::all([TestEnum::TWO]), true);
    }

    public function testReadableName(): void
    {
        $readableEnum = ReadableEnum::roleAdmin();

        self::assertSame('role_admin', $readableEnum->getName());
        self::assertSame('Role Admin', $readableEnum->getReadableName());
    }

    public function testCompositeCustomValue(): void
    {
        self::assertEquals('This is two description for one', TestEnum::one()->getDescriptionTwo());
    }

    public function testPrivateConstants(): void
    {
        $enum = null;

        try {
            $enum = PrivateConstEnum::iAmPrivate();
        } catch (\Throwable $e) {
        }

        self::assertInstanceOf(PrivateConstEnum::class, $enum);
        self::assertTrue($enum->isIAmPrivate());
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

        $this->assertInstanceOf(TestEnum::class, $unserialized);
        $this->assertTrue($enum->eq($unserialized));
    }

    public function testFrom(): void
    {
        self::assertInstanceOf(Enum::class, TestEnum::fromName('yo'));
    }
}

/**
 * @method static TestEnum one()
 * @method static TestEnum two()
 * @method string getDescription()
 * @method string getDescriptionTwo()
 * @method static self fromName(string $name)
 */
class TestEnum extends Enum
{
    public const ONE = 1;
    public const TWO = 2;

    private static $name = [
        self::ONE => 'yo',
    ];

    private static $description = [
        self::TWO => 'This is a description for TestEnum::TWO',
    ];

    private static $descriptionTwo = [
        self::ONE => 'This is two description for one',
    ];
}

/**
 * @method static AnotherTestEnum one()
 */
class AnotherTestEnum extends Enum
{
    public const ONE = 1;
}

class EmptyEnum extends Enum
{
}

class StringValueEnum extends Enum
{
    public const ONE = 1;
    public const WRONG_VALUE = 'string';
}

/**
 * @method static ReadableEnum roleAdmin()
 */
class ReadableEnum extends Enum
{
    public const ROLE_ADMIN = 1;
}

/**
 * @method static PrivateConstEnum iAmPrivate()
 * @method bool   isIAmPrivate()
 */
class PrivateConstEnum extends Enum
{
    private const I_AM_PRIVATE = 1;
}
