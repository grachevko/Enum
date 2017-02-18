<?php

namespace Grachevko\Enum;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class Utils
{
    /**
     * @param string $string
     *
     * @return string
     */
    public static function stringToConstant(string $string): string
    {
        return strtoupper(preg_replace('/\B([A-Z])/', '_$1', $string));
    }
}
