<?php

declare(strict_types=1);

namespace Premier\Enum\Tests;

use Premier\Enum\Enum;

/**
 * @method static TestEnum one()
 * @method static TestEnum two()
 * @method string toDescription()
 * @method string toDescriptionTwo()
 * @method static self fromName(string $name)
 * @method static self undefinedMethod()
 */
class TestEnum extends Enum
{
    private const ONE = 1;
    private const TWO = 2;

    /**
     * @var array
     */
    private static $name = [
        self::ONE => 'yo',
        self::TWO => 'Double yo',
    ];

    /**
     * @var array
     */
    private static $identifier = [
        self::ONE => 'uno',
        self::TWO => 'duo',
    ];

    /**
     * @var array
     */
    private static $description = [
        self::ONE => 'This is a description for TestEnum::ONE',
        self::TWO => 'This is a description for TestEnum::TWO',
    ];

    /**
     * @var array
     */
    private static $descriptionTwo = [
        self::ONE => 'This is two description for one',
        self::TWO => 'This is two description for two',
    ];
}
