<?php

namespace Grachevko\Enum\Tests;

use Grachevko\Enum\Gender;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
class GenderTest extends TestCase
{
    public function testEnumStringArg(): void
    {
        /** @var int $id */
        $id = '1';

        self::assertSame(1, Gender::create($id)->getId());
    }

    public function testEnumIsMale(): void
    {
        $male = Gender::male();
        self::assertTrue($male->isMale());
        self::assertFalse($male->isFemale());
        self::assertFalse($male->isUnapplicable());
        self::assertSame('male', $male->getName());
    }

    public function testEnumIsFemale(): void
    {
        $female = Gender::female();
        self::assertTrue($female->isFemale());
        self::assertFalse($female->isMale());
        self::assertFalse($female->isUnapplicable());
        self::assertSame('female', $female->getName());
    }

    public function testEnumToArray(): void
    {
        self::assertSame([Gender::male()->getId() => Gender::male()], Gender::male()->toArray());
    }

    public function testEnumCallStatic(): void
    {
        self::assertSame(Gender::create(0), Gender::unknown());
        self::assertSame(Gender::create(1), Gender::male());
        self::assertSame(Gender::create(2), Gender::female());
        self::assertSame(Gender::create(9), Gender::unapplicable());
    }

    public function testEnumCall(): void
    {
        self::assertTrue(Gender::male()->isMale());
        self::assertFalse(Gender::female()->isMale());
    }
}
