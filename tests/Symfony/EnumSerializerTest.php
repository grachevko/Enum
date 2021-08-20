<?php

declare(strict_types=1);

namespace Premier\Enum\Tests\Symfony;

use PHPUnit\Framework\TestCase;
use Premier\Enum\Enum;
use Premier\Enum\Gender;
use Premier\Enum\Symfony\EnumNormalizer;
use Generator;

final class EnumSerializerTest extends TestCase
{
    /**
     * @dataProvider enums
     */
    public function testNormalize(Enum $enum, int $expected): void
    {
        self::assertSame($expected, (new EnumNormalizer())->normalize($enum));
    }

    /**
     * @dataProvider enums
     */
    public function testDenormalize(Enum $expected, int $data): void
    {
        self::assertSame($expected, (new EnumNormalizer())->denormalize($data, $expected::class));
    }

    public function enums(): Generator
    {
        $male = Gender::male();
        $female = Gender::female();

        yield [$male, $male->toId()];
        yield [$female, $female->toId()];
    }
}
