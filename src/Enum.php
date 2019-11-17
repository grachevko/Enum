<?php

namespace Premier\Enum;

use function array_diff;
use function array_flip;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use BadMethodCallException;
use function ctype_upper;
use function get_class;
use function in_array;
use InvalidArgumentException;
use function is_int;
use function lcfirst;
use LogicException;
use function preg_replace;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Serializable;
use function sprintf;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;

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

    final public function __toString(): string
    {
        return (string) $this->getId();
    }

    /**
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
            return $this->eq(static::create($reflection->getConstant(self::stringToConstant(substr($name, 2)))));
        }

        if (0 === strpos($name, 'get') && ctype_upper($name[3])) {
            return $this->get(lcfirst(substr($name, 3)));
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, static::class));
    }

    /**
     * @throws ReflectionException
     * @throws BadMethodCallException
     * @throws LogicException
     *
     * @return static
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $reflectionClass = self::getReflection();

        $const = self::stringToConstant($name);
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
     * @throws ReflectionException
     *
     * @return static
     */
    final public static function create(int $id): self
    {
        return self::$instances[static::class][$id] ?? self::$instances[static::class][$id] = new static($id);
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     *
     * @return static
     */
    final public static function from(string $property, $value): self
    {
        $id = 'id' === $property ? $value : array_flip(self::getReflectionProperty($property)->getValue())[$value];

        return static::create($id);
    }

    final public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws ReflectionException
     */
    final public function getName(): string
    {
        if (self::getReflection()->hasProperty('name')) {
            return $this->get('name');
        }

        return strtolower(array_flip(self::getReflection()->getConstants())[$this->getId()]);
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    final public function get(string $property)
    {
        if ('id' === $property) {
            return $this->id;
        }

        if (!self::getReflection()->hasProperty($property)) {
            throw new InvalidArgumentException(sprintf('Property "%s" not exist at class "%s"', $property, static::class));
        }

        return self::getReflectionProperty($property)->getValue()[$this->getId()];
    }

    /**
     * @param Enum[]|int[]|string[] $values
     *
     * @throws ReflectionException
     *
     * @return static[]
     */
    final public static function all(array $values = [], bool $reverse = false, string $property = 'id'): array
    {
        $values = array_map(static function ($value) {
            return $value instanceof self ? $value->getId() : $value;
        }, $values);

        if ('id' === $property) {
            $all = array_values(self::getReflection()->getConstants());
        } else {
            $all = array_values(self::getReflectionProperty($property)->getValue());
        }

        if ([] === $values) {
            $values = $all;
        } else {
            $values = $reverse ? array_values(array_diff($all, $values)) : $values;
        }

        return array_map(static function ($id) use ($property) {
            return static::from($property, $id);
        }, $values);
    }

    final public function eq(self $enum): bool
    {
        return static::class === get_class($enum) && $enum->getId() === $this->getId();
    }

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
     * @throws InvalidArgumentException
     */
    private static function stringToConstant(string $string): string
    {
        $constant = preg_replace('/\B([A-Z])/', '_$1', $string);

        if (null === $constant) {
            throw new InvalidArgumentException(sprintf('preg_replace return null for string "%s"', $string));
        }

        return strtoupper($constant);
    }

    /**
     * @throws ReflectionException
     * @throws LogicException
     */
    private static function getReflection(): ReflectionClass
    {
        $class = static::class;

        return self::$reflections[$class] ?? self::$reflections[$class] = self::validate(new ReflectionClass($class));
    }

    private static function getReflectionProperty(string $property): ReflectionProperty
    {
        self::getReflection();
        $class = static::class;

        return self::$properties[$class][$property];
    }

    /**
     * @throws LogicException
     */
    private static function validate(ReflectionClass $reflection): ReflectionClass
    {
        $constants = $reflection->getConstants();

        if ([] === $constants) {
            throw new LogicException(sprintf('Class %s must define Constants', static::class));
        }

        foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
            if (false === $reflectionConstant->isPrivate()) {
                throw new LogicException(sprintf('Constant "%s" of class "%s" must be private by design.', $reflectionConstant->getName(), static::class, ));
            }

            if (!is_int($reflectionConstant->getValue())) {
                throw new LogicException(sprintf('Constants "%s" of class "%s" must be type of integer by design.', $reflectionConstant->getName(), static::class, ));
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            self::$properties[static::class][$property->getName()] = $property;

            if ($property->isPublic()) {
                throw new LogicException(sprintf('Property "%s" of class "%s" must be private or protected by design.', $property->getName(), static::class, ));
            }

            if (!$property->isStatic()) {
                throw new LogicException(sprintf('Property "%s" of class "%s" must be static by design.', $property->getName(), static::class, ));
            }

            if (array_values($constants) !== array_keys($property->getValue())) {
                throw new LogicException(sprintf('Property "%s" of class "%s" must have values for all constants by design.', $property->getName(), static::class));
            }
        }

        return $reflection;
    }
}
