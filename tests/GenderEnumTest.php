<?php

namespace Grachevko\Enum\Tests;

use Grachevko\Enum\GenderEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
class GenderEnumTest extends TestCase
{
    public function testEnumStringArg()
    {
        self::assertSame(1, (new GenderEnum('1'))->getId());
    }

    public function testEnumEmptyClass()
    {
        $this->expectException(\LogicException::class);

        new EmptyEnum(10);
    }

    public function testEnumIsMale()
    {
        $male = new GenderEnum(GenderEnum::MALE);
        self::assertTrue($male->isMale());
        self::assertFalse($male->isFemale());
        self::assertFalse($male->isUnapplicable());
        self::assertSame('MALE', $male->getName());
        self::assertSame('Male', $male->getReadableName());
    }

    public function testEnumIsFemale()
    {
        $female = new GenderEnum(GenderEnum::FEMALE);
        self::assertTrue($female->isFemale());
        self::assertFalse($female->isMale());
        self::assertFalse($female->isUnapplicable());
        self::assertSame('FEMALE', $female->getName());
        self::assertSame('Female', $female->getReadableName());
    }

    public function testEnumToArray()
    {
        self::assertEquals([GenderEnum::MALE => GenderEnum::male()], GenderEnum::male()->toArray());
    }

    public function testEnumCallStatic()
    {
        self::assertEquals(new GenderEnum(GenderEnum::UNKNOWN), GenderEnum::unknown());
        self::assertEquals(new GenderEnum(GenderEnum::MALE), GenderEnum::male());
        self::assertEquals(new GenderEnum(GenderEnum::FEMALE), GenderEnum::female());
        self::assertEquals(new GenderEnum(GenderEnum::UNAPPLICABLE), GenderEnum::unapplicable());

        $this->expectException(\BadMethodCallException::class);
        self::throwException(GenderEnum::{'boom'}());
    }

    public function testEnumCall()
    {
        self::assertTrue(GenderEnum::male()->isMale());
        self::assertFalse(GenderEnum::female()->isMale());

        $this->expectException(\InvalidArgumentException::class);
        self::throwException(GenderEnum::unapplicable()->{'isBoom'}());

        $this->expectException(\BadMethodCallException::class);
        self::throwException(GenderEnum::unapplicable()->{'boom'}());
    }
}
