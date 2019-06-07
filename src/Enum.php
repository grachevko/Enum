<?php

namespace Grachevko\Enum;

use BadMethodCallException;
use function in_array;
use InvalidArgumentException;
use function is_int;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Serializable;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
abstract class Enum implements Serializable
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
     * @var ReflectionProperty[][]
     */
    private static $properties = [];

    /**
     * @var Enum[][]
     */
    private static $instances = [];

    /**
     * @param int $id
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function __construct(int $id)
    {
        if (!in_array($id, self::getReflection()->getConstants(), true)) {
            throw new InvalidArgumentException(sprintf('Undefined enum "%s" of class "%s"', $id, static::class));
        }

        $this->id = $id;
    }

    /**
     * @return string
     */
    final public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     *
     * @return bool|string
     */
    final public function __call(string $name, array $arguments)
    {
        $reflection = self::getReflection();

        if (0 === strpos($name, 'is') && ctype_upper($name[2])) {
            $const = Utils::stringToConstant(substr($name, 2));

            if (!$reflection->hasConstant($const)) {
                throw new InvalidArgumentException(
                    sprintf('Undefined constant "%s" or method "%s" in class "%s"', $const, $name, static::class)
                );
            }

            return $this->eq(static::create($reflection->getConstant($const)));
        }

        $property = lcfirst(substr($name, 3));
        if (0 === strpos($name, 'get') && ctype_upper($name[3])) {
            if (!$reflection->hasProperty($property)) {
                throw new InvalidArgumentException(
                    sprintf('Undefined property "%s" or method "%s" in class "%s"', $property, $name, static::class)
                );
            }

            return $this->get($property);
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, static::class));
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @throws ReflectionException
     * @throws BadMethodCallException
     * @throws LogicException
     *
     * @return static
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $reflectionClass = self::getReflection();

        $const = Utils::stringToConstant($name);
        $constants = $reflectionClass->getConstants();

        if (array_key_exists($const, $constants)) {
            return static::create($constants[$const]);
        }

        if (0 === strpos($name, 'from') && ctype_upper($name[4])) {
            return static::from(lcfirst(substr($name, 4)), $arguments[0]);
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, static::class));
    }

    /**
     * @param int $id
     *
     * @throws ReflectionException
     *
     * @return static
     */
    final public static function create(int $id): self
    {
        return self::$instances[static::class][$id] ?? self::$instances[static::class][$id] = new static($id);
    }

    /**
     * @param string $property
     * @param mixed  $value
     *
     * @throws ReflectionException
     *
     * @return Enum
     */
    final public static function from(string $property, $value): self
    {
        return static::create(array_flip(self::$properties[static::class][$property]->getValue())[$value]);
    }

    /**
     * @return int
     */
    final public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws ReflectionException
     *
     * @return string
     */
    final public function getName(): string
    {
        if (self::getReflection()->hasProperty('name')) {
            return $this->get('name');
        }

        return strtolower(array_flip(self::getReflection()->getConstants())[$this->getId()]);
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    final public function get(string $property)
    {
        return self::$properties[static::class][$property]->getValue()[$this->getId()];
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @throws ReflectionException
     *
     * @return array
     */
    final public static function all(array $ids = [], $reverse = false): array
    {
        $all = array_values(self::getReflection()->getConstants());

        if (!$ids) {
            $ids = $all;
        } else {
            $ids = $reverse ? array_diff($all, $ids) : $ids;
        }

        return array_map(static function (int $id) {
            return static::create($id);
        }, $ids);
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    final public function in(array $array): bool
    {
        return in_array($this->getId(), $array, true);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    final public function is(int $id): bool
    {
        return $this->getId() === $id;
    }

    /**
     * @param Enum $enum
     *
     * @return bool
     */
    final public function eq(Enum $enum): bool
    {
        return $this instanceof $enum && $enum->getId() === $this->getId();
    }

    /**
     * @return string
     */
    final public function serialize(): string
    {
        return (string) $this->getId();
    }

    /**
     * @param string $serialized
     */
    final public function unserialize($serialized): void
    {
        $this->id = (int) $serialized;
    }

    /**
     * @return array
     */
    final public function toArray(): array
    {
        return [$this->getId() => $this];
    }

    /**
     * @throws ReflectionException
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

        $reflection = new ReflectionClass($class);

        $constants = $reflection->getConstants();

        if ([] === $constants) {
            throw new LogicException(sprintf('Class %s must define Constants', static::class));
        }

        foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
            if (false === $reflectionConstant->isPrivate()) {
                throw new LogicException('All constants must be private by design.');
            }

            if (!is_int($reflectionConstant->getValue())) {
                throw new LogicException('All enum constants must be type of integer by design.');
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            self::$properties[static::class][$property->getName()] = $property;

            if ($property->isPublic()) {
                throw new LogicException('All properties must be private or protected by design.');
            }

            if (!$property->isStatic()) {
                throw new LogicException('All properties must be static by design.');
            }

            if (array_values($constants) !== array_keys($property->getValue())) {
                throw new LogicException('Properties must have values for all constants by design.');
            }
        }

        return self::$reflections[$class] = $reflection;
    }
}
