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
        $constants = self::getReflection()->getConstants();

        if (0 === strpos($name, 'is') && ctype_upper($name[2])) {
            $const = Utils::stringToConstant(substr($name, 2, \strlen($name)));

            if (!array_key_exists($const, $constants)) {
                throw new InvalidArgumentException(sprintf(
                        'Undefined constant "%s" in class "%s" to use method "%s"', $const, static::class, $name)
                );
            }

            return $this->eq(new static($constants[$const]));
        }

        $property = lcfirst(substr($name, 3));
        if (0 === strpos($name, 'get') && property_exists(static::class, $property) && ctype_upper($name[3])) {
            $values = static::${$property};
            if (array_key_exists($id, $values)) {
                return $values[$id];
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
     *
     * @return static
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $const = Utils::stringToConstant($name);
        $constants = self::getReflection()->getConstants();

        if (array_key_exists($const, $constants)) {
            return new static($constants[$const]);
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
        $id = $this->getId();

        if (property_exists(static::class, 'name')) {
            $values = static::${'name'};
            if ($values && array_key_exists($id, $values)) {
                return $values[$id];
            }
        }

        return strtolower(array_flip(self::getReflection()->getConstants())[$id]);
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
}
