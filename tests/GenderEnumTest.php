<?php

namespace Grachevko\Enum\Tests;

use BadMethodCallException;
use Grachevko\Enum\GenderEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
class GenderEnumTest extends TestCase
{
    public function testEnumStringArg(): void
    {
        /** @var int $id */
        $id = '1';

        self::assertSame(1, GenderEnum::create($id)->getId());
    }

    public function testEnumIsMale(): void
    {
        $male = GenderEnum::male();
        self::assertTrue($male->isMale());
        self::assertFalse($male->isFemale());
        self::assertFalse($male->isUnapplicable());
        self::assertSame('male', $male->getName());
        self::assertSame('Male', $male->getReadableName());
    }

    public function testEnumIsFemale(): void
    {
        $female = GenderEnum::female();
        self::assertTrue($female->isFemale());
        self::assertFalse($female->isMale());
        self::assertFalse($female->isUnapplicable());
        self::assertSame('female', $female->getName());
        self::assertSame('Female', $female->getReadableName());
    }

    public function testEnumToArray(): void
    {
        self::assertSame([GenderEnum::male()->getId() => GenderEnum::male()], GenderEnum::male()->toArray());
    }

    public function testEnumCallStatic(): void
    {
        self::assertSame(GenderEnum::create(0), GenderEnum::unknown());
        self::assertSame(GenderEnum::create(1), GenderEnum::male());
        self::assertSame(GenderEnum::create(2), GenderEnum::female());
        self::assertSame(GenderEnum::create(9), GenderEnum::unapplicable());

        $this->expectException(BadMethodCallException::class);
        self::throwException(GenderEnum::{'boom'}());
    }

    public function testEnumCall(): void
    {
        self::assertTrue(GenderEnum::male()->isMale());
        self::assertFalse(GenderEnum::female()->isMale());

        $this->expectException(InvalidArgumentException::class);
        self::throwException(GenderEnum::unapplicable()->{'isBoom'}());

        $this->expectException(BadMethodCallException::class);
        self::throwException(GenderEnum::unapplicable()->{'boom'}());
    }
}
