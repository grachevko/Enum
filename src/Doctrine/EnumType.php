<?php

declare(strict_types=1);

namespace Premier\Enum\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Premier\Enum\Enum;
use function assert;
use function filter_var;
use function is_subclass_of;
use function sprintf;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumType extends Type
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $property;

    /**
     * @psalm-param class-string<Enum> $class
     */
    public static function register(string $class, string $name, string $property = 'id'): void
    {
        if (!is_subclass_of($class, Enum::class, true)) {
            throw new InvalidArgumentException(sprintf('%s is not child of %s', $class, Enum::class));
        }

        Type::addType($name, self::class);
        $type = Type::getType($name);
        assert($type instanceof self);

        $type->class = $class;
        $type->name = $name;
        $type->property = $property;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ('id' === $this->property) {
            return $platform->getSmallIntTypeDeclarationSQL($column);
        }

        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Enum
    {
        if (null === $value) {
            return null;
        }

        $class = $this->class;

        if ($value instanceof $class) {
            assert($value instanceof Enum);

            return $value;
        }

        if ('id' === $this->property) {
            if (false === $id = filter_var($value, FILTER_VALIDATE_INT)) {
                throw ConversionException::conversionFailed($value, $this->getName());
            }

            $value = $id;
        }

        /** @var callable $callable */
        $callable = [$class, 'from'];
        $enum = $callable($this->property, $value);

        if (!$enum instanceof $class) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        assert($enum instanceof Enum);

        return $enum;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $class = $this->class;

        if (!$value instanceof $class) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        assert($value instanceof Enum);

        return $value->to($this->property);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
