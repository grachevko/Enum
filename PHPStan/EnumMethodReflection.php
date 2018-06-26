<?php

namespace Grachevko\Enum\PHPStan;

use Grachevko\Enum\Utils;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\VoidType;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumMethodReflection implements MethodReflection
{
    /**
     * @var ClassReflection
     */
    private $classReflection;

    /**
     * @var string
     */
    private $name;

    /**
     * @var FunctionVariant[]|null
     */
    private $variants;

    public function __construct(ClassReflection $classReflection, string $methodName)
    {
        $this->classReflection = $classReflection;
        $this->name = $methodName;
    }

    public function getVariants(): array
    {
        if (null === $this->variants) {
            $this->variants = [
                new FunctionVariant([], false, new VoidType()),
            ];
        }

        return $this->variants;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return $this->classReflection->getNativeReflection()->hasConstant(Utils::stringToConstant($this->name));
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
