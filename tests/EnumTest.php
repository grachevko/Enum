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
        self::assertSame('TWO', TestEnum::two()->getName());
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

        self::assertSame('ROLE_ADMIN', $readableEnum->getName());
        self::assertSame('Role Admin', $readableEnum->getReadableName());
    }
}

/**
 * @method static TestEnum one()
 * @method static TestEnum two()
 * @method string getDescription()
 */
class TestEnum extends Enum
{
    const ONE = 1;
    const TWO = 2;

    protected static $name = [
        self::ONE => 'yo',
    ];

    protected static $description = [
        self::TWO => 'This is a description for TestEnum::TWO',
    ];
}

class EmptyEnum extends Enum
{
}

class StringValueEnum extends Enum
{
    const ONE = 1;
    const WRONG_VALUE = 'string';
}

/**
 * @method static ReadableEnum roleAdmin()
 */
class ReadableEnum extends Enum
{
    const ROLE_ADMIN = 1;
}
