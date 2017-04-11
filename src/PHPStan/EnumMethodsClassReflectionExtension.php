<?php

namespace Grachevko\Enum\PHPStan;

use Grachevko\Enum\Enum;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class EnumMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (!$classReflection->isSubclassOf(Enum::class)) {
            return false;
        }

        $property = 0 === strpos($methodName, 'get') ? lcfirst(substr($methodName, 3)) : $methodName;

        return $classReflection->hasProperty($property);
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new EnumMethodReflection($classReflection, $methodName);
    }
}
