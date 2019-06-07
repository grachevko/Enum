<?php

namespace Grachevko\Enum;

use InvalidArgumentException;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class Utils
{
    /**
     * @param string $string
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function stringToConstant(string $string): string
    {
        $constant = preg_replace('/\B([A-Z])/', '_$1', $string);

        if (null === $constant) {
            throw new InvalidArgumentException(sprintf('preg_replace return null for string "%s"', $string));
        }

        return strtoupper($constant);
    }
}
