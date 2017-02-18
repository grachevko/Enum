<?php

namespace Grachevko\Enum;

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
     * @var \ReflectionClass[]
     */
    private static $reflections;

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(int $id)
    {
        self::initialize();

        if (!in_array($id, self::$reflections[static::class]->getConstants(), true)) {
            throw new \InvalidArgumentException(sprintf('Undefined enum "%s" of class "%s"', $id, get_called_class()));
        }

        $this->id = $id;
    }

    /**
     * @throws \LogicException
     */
    private static function initialize(): void
    {
        $class = static::class;

        if (self::$reflections[$class]) {
            return;
        }

        self::$reflections[$class] = $reflection = new \ReflectionClass($class);

        $constants = $reflection->getConstants();
        if (!$constants) {
            throw new \LogicException(sprintf('Class %s must define Constants', get_called_class()));
        }

        foreach ($constants as $value) {
            if (!is_int($value)) {
                throw new \LogicException('All enum constants must be in integer type');
            }
        }
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

        return strtolower(array_flip(self::$reflections[static::class]->getConstants())[$id]);
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @return array
     */
    public static function all(array $ids = [], $reverse = false): array
    {
        $all = array_values(self::$reflections[static::class]->getConstants());

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
        return in_array($this->getId(), $array, true);
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
        return $enum->getId() === $this->getId();
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
     *
     * @return static
     */
    public function unserialize($serialized): Enum
    {
        return new static((int) $serialized);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [$this->getId() => $this];
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
     * @return bool|string
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __call(string $name, array $arguments)
    {
        self::initialize();

        $id = $this->getId();
        $constants = self::$reflections[static::class]->getConstants();

        if (0 === strpos($name, 'is') && ctype_upper($name[2])) {
            $const = Utils::stringToConstant(substr($name, 2, strlen($name)));

            if (!array_key_exists($const, $constants)) {
                throw new \InvalidArgumentException(sprintf(
                        'Undefined constant "%s" in class "%s" to use method "%s"', $const, get_called_class(), $name)
                );
            }

            return $id === constant(static::class.'::'.$const);
        }

        if (
            0 === strpos($name, 'get')
            && ctype_upper($name[3])
            && property_exists(static::class, $property = strtolower(substr($name, 3)))
        ) {
            $values = static::${$property};
            if (array_key_exists($id, $values)) {
                return $values[$id];
            }

            throw new \LogicException(sprintf(
                'Undefined value in property "%s" for "%s" constant',
                $property,
                $constants[$id]
            ));
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, get_called_class()));
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        self::initialize();

        $const = Utils::stringToConstant($name);

        if (array_key_exists($const, self::$reflections[static::class]->getConstants())) {
            return new static(constant(static::class.'::'.$const));
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, get_called_class()));
    }
}
