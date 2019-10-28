<?php

namespace Premier\Enum;

use function array_keys;
use function array_values;
use BadMethodCallException;
use function in_array;
use InvalidArgumentException;
use function is_int;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Serializable;
use function sprintf;

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
            throw new InvalidArgumentException(
                sprintf('Undefined enum "%s" of class "%s"', $id, static::class)
            );
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
            return $this->eq(static::create($reflection->getConstant(self::stringToConstant(substr($name, 2)))));
        }

        if (0 === strpos($name, 'get') && ctype_upper($name[3])) {
            return $this->get(lcfirst(substr($name, 3)));
        }

        throw new BadMethodCallException(
            sprintf('Undefined method "%s" in class "%s"', $name, static::class)
        );
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

        $const = self::stringToConstant($name);
        $constants = $reflectionClass->getConstants();

        if (array_key_exists($const, $constants)) {
            return static::create($constants[$const]);
        }

        if (0 === strpos($name, 'from') && ctype_upper($name[4])) {
            return static::from(lcfirst(substr($name, 4)), $arguments[0]);
        }

        throw new BadMethodCallException(
            sprintf('Undefined method "%s" in class "%s"', $name, static::class)
        );
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
     * @return static
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
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    final public function get(string $property)
    {
        if (!array_key_exists($property, self::$properties[static::class])) {
            throw new InvalidArgumentException(
                sprintf('Property "%s" not exist at class "%s"', $property, static::class)
            );
        }

        return self::$properties[static::class][$property]->getValue()[$this->getId()];
    }

    /**
     * @param Enum[]|int[]|string[] $ids
     * @param bool                  $reverse
     *
     * @throws ReflectionException
     *
     * @return static[]
     */
    final public static function all(array $ids = [], bool $reverse = false): array
    {
        $ids = array_map(static function ($id) {
            return $id instanceof Enum ? $id->getId() : (int) $id;
        }, $ids);

        $all = array_values(self::getReflection()->getConstants());

        if ([] === $ids) {
            $ids = $all;
        } else {
            $ids = $reverse ? array_diff($all, $ids) : $ids;
        }

        return array_map(static function (int $id) {
            return static::create($id);
        }, $ids);
    }

    /**
     * @param Enum $enum
     *
     * @return bool
     */
    final public function eq(Enum $enum): bool
    {
        return static::class === get_class($enum) && $enum->getId() === $this->getId();
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
     * @param string $string
     *
     * @throws InvalidArgumentException
     *
     * @return string
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
     *
     * @return ReflectionClass
     */
    private static function getReflection(): ReflectionClass
    {
        $class = static::class;

        return self::$reflections[$class] ?? self::$reflections[$class] = self::validate(new ReflectionClass($class));
    }

    /**
     * @param ReflectionClass $reflection
     *
     * @throws LogicException
     *
     * @return ReflectionClass
     */
    private static function validate(ReflectionClass $reflection): ReflectionClass
    {
        $constants = $reflection->getConstants();

        if ([] === $constants) {
            throw new LogicException(sprintf('Class %s must define Constants', static::class));
        }

        foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
            if (false === $reflectionConstant->isPrivate()) {
                throw new LogicException(sprintf(
                    'Constant "%s" of class "%s" must be private by design.',
                    $reflectionConstant->getName(),
                    static::class,
                    ));
            }

            if (!is_int($reflectionConstant->getValue())) {
                throw new LogicException(sprintf(
                    'Constants "%s" of class "%s" must be type of integer by design.',
                    $reflectionConstant->getName(),
                    static::class,
                    ));
            }
        }

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            self::$properties[static::class][$property->getName()] = $property;

            if ($property->isPublic()) {
                throw new LogicException(sprintf(
                    'Property "%s" of class "%s" must be private or protected by design.',
                    $property->getName(),
                    static::class,
                    ));
            }

            if (!$property->isStatic()) {
                throw new LogicException(sprintf(
                    'Property "%s" of class "%s" must be static by design.',
                    $property->getName(),
                    static::class,
                    ));
            }

            if (array_values($constants) !== array_keys($property->getValue())) {
                throw new LogicException(sprintf(
                    'Property "%s" of class "%s" must have values for all constants by design.',
                    $property->getName(),
                    static::class
                ));
            }
        }

        return $reflection;
    }
}
