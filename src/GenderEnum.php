<?php

namespace Grachev\Enum;

/**
 * @author Konstantin Grachev <ko@grachev.io>
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
    const
        UNKNOWN = 0,
        MALE = 1,
        FEMALE = 2,
        UNAPPLICABLE = 9;

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
