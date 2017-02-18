<?php

namespace Grachevko\Enum;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
abstract class Enum implements \Serializable
{
    /**
     * @var array
     */
    protected static $names = [];

    /**
     * @var array
     */
    protected static $description = [];

    /**
     * @var string
     */
    protected static $prefix;

    /**
     * @var string
     */
    protected static $postfix;

    /**
     * @var array
     */
    private static $meta = [];

    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function __construct($id)
    {
        $this->setId((int) $id);
    }

    /**
     * @throws \LogicException
     */
    private static function initialize()
    {
        if (self::isInitialized()) {
            return;
        }

        $class = static::class;
        $constants = (new \ReflectionClass($class))->getConstants();

        if (!$constants) {
            throw new \LogicException(sprintf('Class %s must define Constants', get_called_class()));
        }

        foreach ($constants as $constant => $value) {
            if (!array_key_exists($value, static::$names)) {
                static::$names[$value] = static::$prefix.strtolower($constant).static::$postfix;
            }
        }

        self::$meta[$class]['ids'] = array_keys(array_flip($constants));
        self::$meta[$class]['constants'] = $constants;
    }

    /**
     * @return bool
     */
    private static function isInitialized()
    {
        if (array_key_exists(static::class, self::$meta)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    private function setId($id)
    {
        if (!self::hasId($id)) {
            throw new \InvalidArgumentException(sprintf('Undefined enum "%s" of class "%s"', $id, get_called_class()));
        }

        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public static function hasId($id)
    {
        return in_array($id, self::getIds(), true);
    }

    /**
     * @return array
     *
     * @throws \LogicException
     */
    public static function getIds()
    {
        self::initialize();

        return self::$meta[static::class]['ids'];
    }

    /**
     * @return array
     *
     * @throws \LogicException
     */
    protected static function getConstants()
    {
        self::initialize();

        return self::$meta[static::class]['constants'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::$names[$this->getId()];
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @return array
     *
     * @throws \LogicException
     */
    public static function getNames(array $ids = [], $reverse = false)
    {
        return self::getProperties('names', $ids, $reverse);
    }

    /**
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getDescription()
    {
        if (!array_key_exists($this->getId(), self::getDescriptions())) {
            throw new \InvalidArgumentException(sprintf('Undefined description for enum "%s"', $this->getId()));
        }

        return static::$description[$this->getId()];
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @return array
     *
     * @throws \LogicException
     */
    public static function getDescriptions(array $ids = [], $reverse = false)
    {
        return self::getProperties('descriptions', $ids, $reverse);
    }

    /**
     * @param string $name
     * @param array  $ids
     * @param bool   $reverse
     *
     * @return array
     *
     * @throws \LogicException
     */
    private static function getProperties($name, array $ids = [], $reverse = false)
    {
        self::initialize();

        $properties = static::${$name};

        if ($ids) {
            $ids = array_flip($ids);

            if ($reverse) {
                $properties = array_diff_key($properties, $ids);
            } else {
                $properties = array_intersect_key($properties, $ids);
            }
        }

        return $properties;
    }

    /**
     * @return int
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function getAnyId()
    {
        $ids = static::getIds();

        return $ids[array_rand($ids, 1)];
    }

    /**
     * @return static|Enum
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function getAny()
    {
        return new static(self::getAnyId());
    }

    /**
     * @param array $ids
     * @param bool  $reverse
     *
     * @return static[]
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function getList(array $ids = null, $reverse = false)
    {
        if (null === $ids) {
            $ids = static::getIds();
        } else {
            $ids = $reverse ? array_diff(static::getIds(), $ids) : $ids;
        }

        $list = [];
        foreach ($ids as $id) {
            $list[$id] = new static($id);
        }

        return $list;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public function in(array $array)
    {
        return in_array($this->getId(), $array, true);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function is($id)
    {
        return $this->getId() === $id;
    }

    /**
     * @param Enum $enum
     *
     * @return bool
     */
    public function eq(Enum $enum)
    {
        return $enum->getId() === $this->getId();
    }

    /**
     * @return int
     */
    public function serialize()
    {
        return $this->getId();
    }

    /**
     * @param int $serialized
     *
     * @return static|Enum
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function unserialize($serialized)
    {
        return new static($serialized);
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
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     */
    public function __call($name, $arguments)
    {
        if (0 === strpos($name, 'is') && ctype_upper(substr($name, 2, 1))) {
            $const = strtoupper(preg_replace('/\B([A-Z])/', '_$1', substr($name, 2, strlen($name))));

            if (!array_key_exists($const, self::getConstants())) {
                throw new \InvalidArgumentException(sprintf('Undefined constant "%s" in class "%s" to use method "%s"', $const, get_called_class(), $name));
            }

            return $this->getId() === constant(static::class.'::'.$const);
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, get_called_class()));
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return static|Enum
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \LogicException
     */
    public static function __callStatic($name, $arguments)
    {
        self::initialize();

        $const = strtoupper(preg_replace('/\B([A-Z])/', '_$1', $name));

        if (array_key_exists($const, self::getConstants())) {
            return new static(constant(static::class.'::'.$const));
        }

        throw new \BadMethodCallException(sprintf('Undefined method "%s" in class "%s"', $name, get_called_class()));
    }
}
