<?php

namespace Grachevko\Enum;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 *
 * @see https://en.wikipedia.org/wiki/ISO/IEC_5218
 *
 * @method static GenderEnum unknown()
 * @method static GenderEnum male()
 * @method static GenderEnum female()
 * @method static GenderEnum unapplicable()
 * @method bool isUnknown()
 * @method bool isMale()
 * @method bool isFemale()
 * @method bool isUnapplicable()
 */
class GenderEnum extends Enum
{
    const UNKNOWN = 0;
    const MALE = 1;
    const FEMALE = 2;
    const UNAPPLICABLE = 9;

    /**
     * @return bool
     */
    public function isDefined()
    {
        return $this->in([
            self::MALE,
            self::FEMALE,
        ]);
    }
}
