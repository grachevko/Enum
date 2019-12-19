<?php

declare(strict_types=1);

namespace Premier\Enum;

use function in_array;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 *
 * @see https://en.wikipedia.org/wiki/ISO/IEC_5218
 *
 * @method static Gender unknown()
 * @method static Gender male()
 * @method static Gender female()
 * @method static Gender unapplicable()
 * @method bool   isUnknown()
 * @method bool   isMale()
 * @method bool   isFemale()
 * @method bool   isUnapplicable()
 */
class Gender extends Enum
{
    private const UNKNOWN = 0;
    private const MALE = 1;
    private const FEMALE = 2;
    private const UNAPPLICABLE = 9;

    public function isDefined(): bool
    {
        return in_array($this->toId(), [self::MALE, self::FEMALE], true);
    }
}
