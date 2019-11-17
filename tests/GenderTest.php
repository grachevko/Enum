<?php

declare(strict_types=1);

namespace Premier\Enum\Tests;

use PHPUnit\Framework\TestCase;
use Premier\Enum\Gender;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
class GenderTest extends TestCase
{
    public function testEnumIsMale(): void
    {
        $male = Gender::male();
        static::assertTrue($male->isMale());
        static::assertFalse($male->isFemale());
        static::assertFalse($male->isUnapplicable());
        static::assertSame('male', $male->getName());
    }

    public function testEnumIsFemale(): void
    {
        $female = Gender::female();
        static::assertTrue($female->isFemale());
        static::assertFalse($female->isMale());
        static::assertFalse($female->isUnapplicable());
        static::assertSame('female', $female->getName());
    }

    public function testEnumCallStatic(): void
    {
        static::assertSame(Gender::create(0), Gender::unknown());
        static::assertSame(Gender::create(1), Gender::male());
        static::assertSame(Gender::create(2), Gender::female());
        static::assertSame(Gender::create(9), Gender::unapplicable());
    }

    public function testEnumCall(): void
    {
        static::assertTrue(Gender::male()->isMale());
        static::assertFalse(Gender::female()->isMale());
    }
}
