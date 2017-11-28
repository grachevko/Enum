<?php

namespace Grachevko\Enum\Tests;

use Grachevko\Enum\Enum;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumTest extends TestCase
{
    public function testEmptyEnum()
    {
        $this->expectException(\LogicException::class);

        new EmptyEnum(1);
    }

    public function testInstantiation()
    {
        self::assertInstanceOf(Enum::class, TestEnum::one());
    }

    public function testIntegerValue()
    {
        $this->expectException(\LogicException::class);

        new StringValueEnum(1);
    }

    public function testNameValue()
    {
        self::assertSame('yo', TestEnum::one()->getName());
        self::assertSame('two', TestEnum::two()->getName());
        self::assertSame('Two', TestEnum::two()->getReadableName());
    }

    public function testCustomPropertyValue()
    {
        self::assertSame('This is a description for TestEnum::TWO', TestEnum::two()->getDescription());
    }

    public function testCustomUndefinedValue()
    {
        $this->expectException(\LogicException::class);

        TestEnum::one()->getDescription();
    }

    public function testAllMethod()
    {
        self::assertEquals([TestEnum::one(), TestEnum::two()], TestEnum::all());
        self::assertEquals([TestEnum::one()], TestEnum::all([TestEnum::ONE]));
        self::assertEquals([TestEnum::two()], TestEnum::all([TestEnum::TWO]), true);
    }

    public function testReadableName()
    {
        $readableEnum = ReadableEnum::roleAdmin();

        self::assertSame('role_admin', $readableEnum->getName());
        self::assertSame('Role Admin', $readableEnum->getReadableName());
    }

    public function testCompositeCustomValue()
    {
        self::assertEquals('This is two description for one', TestEnum::one()->getDescriptionTwo());
    }

    public function testPrivateConstants()
    {
        $enum = null;

        try {
            $enum = PrivateConstEnum::iAmPrivate();
        } catch (\Throwable $e) {
        }

        self::assertInstanceOf(PrivateConstEnum::class, $enum);
    }
}

/**
 * @method static TestEnum one()
 * @method static TestEnum two()
 * @method string getDescription()
 * @method string getDescriptionTwo()
 */
class TestEnum extends Enum
{
    public const ONE = 1;
    public const TWO = 2;

    protected static $name = [
        self::ONE => 'yo',
    ];

    protected static $description = [
        self::TWO => 'This is a description for TestEnum::TWO',
    ];

    protected static $descriptionTwo = [
        self::ONE => 'This is two description for one',
    ];
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
 * @method static iAmPrivate()
 */
class PrivateConstEnum extends Enum
{
    private const I_AM_PRIVATE = 1;
}
