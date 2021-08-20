<?php

declare(strict_types=1);

namespace Premier\Enum\Tests\Doctrine;

use Doctrine\DBAL\Platforms\SQLAnywherePlatform;
use Doctrine\DBAL\Types\Type;
use Generator;
use PHPUnit\Framework\TestCase;
use Premier\Enum\Doctrine\EnumType;
use Premier\Enum\Enum;
use Premier\Enum\Tests\TestEnum;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumTypeTest extends TestCase
{
    /**
     * @var EnumTestPlatform
     */
    private static $platform;

    public static function setUpBeforeClass(): void
    {
        self::$platform = new EnumTestPlatform();
    }

    public function testRegister(): void
    {
        EnumType::register(TestEnum::class, 'test_enum');

        self::assertInstanceOf(EnumType::class, Type::getType('test_enum'));
    }

    /**
     * @param mixed $value
     *
     * @dataProvider conversionDataProvider
     */
    public function testConversion(Type $type, Enum $enum, $value): void
    {
        self::assertSame($value, $type->convertToDatabaseValue($enum, self::$platform));
        self::assertSame($enum, $type->convertToPHPValue($value, self::$platform));
    }

    public function conversionDataProvider(): Generator
    {
        yield [$this->getType('test_int_enum'), TestEnum::one(), 1];
        yield [$this->getType('test_int_enum'), TestEnum::two(), 2];

        yield [$this->getType('test_string_enum', 'identifier'), TestEnum::one(), 'uno'];
        yield [$this->getType('test_string_enum'), TestEnum::two(), 'duo'];
    }

    /**
     * @dataProvider declarationDataProvider
     */
    public function testDeclaration(EnumType $type, string $declaration): void
    {
        self::assertSame($declaration, $type->getSQLDeclaration([], self::$platform));
    }

    public function declarationDataProvider(): Generator
    {
        yield [$this->getType('test_int_enum'), 'smallint'];
        yield [$this->getType('test_string_enum', 'identifier'), 'varchar'];
    }

    private function getType(string $name, string $property = 'id'): Type
    {
        if (!Type::hasType($name)) {
            EnumType::register(TestEnum::class, $name, $property);
        }

        return Type::getType($name);
    }
}

/**
 * @psalm-suppress DeprecatedClass
 */
class EnumTestPlatform extends SQLAnywherePlatform
{
    public function getVarcharTypeDeclarationSQL(array $column): string
    {
        return 'varchar';
    }

    public function getSmallIntTypeDeclarationSQL(array $column): string
    {
        return 'smallint';
    }
}
