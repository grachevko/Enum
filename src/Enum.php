<?php

namespace Grachevko\Enum;

use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
abstract class Enum implements \Serializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ReflectionClass[]
     */
    private static $reflections = [];

    /**
     * @param int $id
     *
     * @throws InvalidArgumentException
     */
    public function __construct(int $id)
    {
        if (!\in_array($id, self::getReflection()->getConstants(), true)) {
            throw new InvalidArgumentException(sprintf('Undefined enum "%s" of class "%s"', $id, static::class));
        }

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     * @throws LogicException
     *
     * @return bool|string
     */
    public function __call(string $name, array $arguments)
    {
        $id = $this->getId();
        $reflectionClass = self::getReflection();
        $constants = $reflectionClass->getConstants();

        if (0 === strpos($name, 'is') && ctype_upper($name[2])) {
            $const = Utils::stringToConstant(substr($name, 2, \strlen($name)));

            if (!array_key_exists($const, $constants)) {
                throw new InvalidArgumentException(sprintf(
                        'Undefined constant "%s" in class "%s" to use method "%s"', $const, static::class, $name)
                );
            }

            return $this->eq(new static($constants[$const]));
        }

        if (0 === strpos($name, 'get') && ctype_upper($name[3])) {
            $property = lcfirst(substr($name, 3));

            if ($value = $this->getPropertyValue($property)) {
                return $value;
            }

            throw new LogicException(sprintf(
                'Undefined value in property "%s" for "%s" constant',
                $property,
                array_flip($constants)[$id]
            ));
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, static::class));
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @throws BadMethodCallException
     * @throws LogicException
     *
     * @return static
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $reflectionClass = self::getReflection();

        $const = Utils::stringToConstant($name);
        $constants = $reflectionClass->getConstants();

        if (array_key_exists($const, $constants)) {
            return new static($constants[$const]);
        }

        if (0 === strpos($name, 'from') && ctype_upper($name[4])) {
            $property = lcfirst(substr($name, 4));

            $value = $arguments[0];
            $values = array_flip(self::getProperty($property));
            if (array_key_exists($value, $values)) {
                return new static($values[$value]);
            }

            throw new LogicException(sprintf(
                'Undefined value "%s" in property "%s"',
                $value,
                $property
            ));
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, static::class));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getPropertyValue('name')
            ?? strtolower(array_flip(self::getReflection()->getConstants())[$this->getId()]);
    }

    /**
     * @return string
     */
    public function getReadableName(): string
    {
        return mb_convert_case(str_replace('_', ' ', $this->getName()), MB_CASE_TITLE);
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @return array
     */
    public static function all(array $ids = [], $reverse = false): array
    {
        $all = array_values(self::getReflection()->getConstants());

        if (!$ids) {
            $ids = $all;
        } else {
            $ids = $reverse ? array_diff($all, $ids) : $ids;
        }

        return array_map(function (int $id) {
            return new static($id);
        }, $ids);
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public function in(array $array): bool
    {
        return \in_array($this->getId(), $array, true);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function is(int $id): bool
    {
        return $this->getId() === $id;
    }

    /**
     * @param Enum $enum
     *
     * @return bool
     */
    public function eq(Enum $enum): bool
    {
        return $this instanceof $enum && $enum->getId() === $this->getId();
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return (string) $this->getId();
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $this->id = (int) $serialized;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getId() => $this];
    }

    /**
     * @throws LogicException
     *
     * @return ReflectionClass
     */
    private static function getReflection(): ReflectionClass
    {
        $class = static::class;

        if (array_key_exists($class, self::$reflections)) {
            return self::$reflections[$class];
        }

        self::$reflections[$class] = $reflection = new ReflectionClass($class);

        $constants = $reflection->getConstants();
        if ([] === $constants) {
            throw new LogicException(sprintf('Class %s must define Constants', static::class));
        }

        foreach ($constants as $value) {
            if (!\is_int($value)) {
                throw new LogicException('All enum constants must be in integer type');
            }
        }

        return $reflection;
    }

    private static function getProperty(string $property): array
    {
        $reflectionClass = self::getReflection();

        $values = [];
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getName() === $property) {
                $reflectionProperty->setAccessible(true);
                $values = $reflectionProperty->getValue();
                $reflectionProperty->setAccessible(false);

                break;
            }
        }

        return $values;
    }

    private function getPropertyValue(string $property)
    {
        return self::getProperty($property)[$this->getId()] ?? null;
    }
}
