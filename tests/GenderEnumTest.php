<?php

namespace Grachevko\Enum\Tests;

use Grachevko\Enum\Enum;
use Grachevko\Enum\GenderEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
class GenderEnumTest extends TestCase
{
    public function testEnumStringArg()
    {
        self::assertEquals(1, (new GenderEnum('1'))->getId());
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
        self::assertEquals('male', $male->getName());
    }

    public function testEnumIsFemale()
    {
        $female = new GenderEnum(GenderEnum::FEMALE);
        self::assertTrue($female->isFemale());
        self::assertFalse($female->isMale());
        self::assertFalse($female->isUnapplicable());
        self::assertEquals('female', $female->getName());
    }

    public function testEnumGetNames()
    {
        self::assertEquals([0 => 'unknown', 1 => 'male', 2 => 'female', 9 => 'unapplicable'], GenderEnum::getNames());
        self::assertEquals([0 => 'unknown'], GenderEnum::getNames([1, 2, 9], true));
        self::assertEquals([1 => 'male'], GenderEnum::getNames([1]));
    }

    public function testEnumToArray()
    {
        self::assertEquals([GenderEnum::MALE => GenderEnum::male()], GenderEnum::male()->toArray());
    }

    public function testEnumGetList()
    {
        $male = GenderEnum::male();
        $female = GenderEnum::female();
        $notKnown = GenderEnum::unknown();
        $notApplicable = GenderEnum::unapplicable();

        self::assertEquals([
            $male->getId() => $male,
            $female->getId() => $female,
            $notKnown->getId() => $notKnown,
            $notApplicable->getId() => $notApplicable,
        ], GenderEnum::getList());

        self::assertEquals([$male->getId() => $male], GenderEnum::getList([1]));
        self::assertEquals([$female->getId() => $female], GenderEnum::getList([0, 1, 9], true));
    }

    public function testEnumGetAnyId()
    {
        for ($i = 0; $i < 20; ++$i) {
            self::assertArrayHasKey(GenderEnum::getAnyId(), array_flip([0, 1, 2, 9]));
        }
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
        self::assertEquals(true, GenderEnum::male()->isMale());
        self::assertEquals(false, GenderEnum::female()->isMale());

        $this->expectException(\InvalidArgumentException::class);
        self::throwException(GenderEnum::unapplicable()->{'isBoom'}());

        $this->expectException(\BadMethodCallException::class);
        self::throwException(GenderEnum::unapplicable()->{'boom'}());
    }

    public function testEnumPrefixAndPostfix()
    {
        self::assertEquals('male', GenderEnum::male()->getName());

        self::assertEquals('prefix.test.postfix', TestEnum::test()->getName());

        self::assertEquals('yo', TestEnum::named()->getName());
    }
}

/**
 * @method static TestEnum test()
 * @method static TestEnum named()
 */
class TestEnum extends Enum
{
    const TEST = 1;

    const NAMED = 2;

    protected static $names = [
        self::NAMED => 'yo',
    ];

    protected static $prefix = 'prefix.';

    protected static $postfix = '.postfix';
}

class EmptyEnum extends Enum
{
}
