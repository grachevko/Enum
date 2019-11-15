# PHP Enum implementation

[![Latest Stable Version](https://poser.pugx.org/premier/enum/v/stable)](https://packagist.org/packages/premier/enum)
[![Total Downloads](https://poser.pugx.org/premier/enum/downloads)](https://packagist.org/packages/premier/enum)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/grachevko/Enum/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/grachevko/Enum/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/grachevko/Enum/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/grachevko/Enum/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/grachevko/Enum/badges/build.png?b=master)](https://scrutinizer-ci.com/g/grachevko/Enum/build-status/master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9bc0fe1b-8b10-44b9-9a71-5819ce7ccaef/big.png)](https://insight.sensiolabs.com/projects/9bc0fe1b-8b10-44b9-9a71-5819ce7ccaef)

## Installation

```
composer require premier/enum
```

## Usage

```php
namespace Premier\Enum;

/**
 * @method static DriveWheel front()
 * @method static DriveWheel rear()
 * @method static DriveWheel allDrive()
 * @method static DriveWheel fromCode(string $code)
 * @method bool   isFront()
 * @method bool   isRear()
 * @method bool   isAllDrive()
 * @method string getCode()
 */
final class DriveWheel extends Enum
{
    private const FRONT = 1;
    private const REAR = 2;
    private const ALL_DRIVE = 3;

    protected static $code = [
        self::FRONT => 'FWD',
        self::REAR => 'RWD',
        self::ALL_DRIVE => 'AWD',
    ];
}

// New instance
$drive = DriveWheel::create(1);
// or
$drive = DriveWheel::front();
// or
$drive = DriveWheel::fromCode('FWD');
// or
$drive = DriveWheel::from('code', 'FWD');

// Array instances
DriveWheel::all(); // [DriveWheel::front(), DriveWheel::rear(), DriveWheel::allDrive()]
DriveWheel::all(['FWD', 'RWD'], $reverse = false, $property = 'code'); // [DriveWheel::front(), DriveWheel::rear()]
DriveWheel::all([1, 2], $reverse = true); // [DriveWheel::allDrive()]

// Methods
$drive->getId();   // 1
$drive->get('id'); // 1
(string) $drive;   // '1'

$drive->getName(); // 'front'

$drive->getCode();   // 'FWD'
$drive->get('code'); // 'FWD'

$drive->isFront(); // true
$drive->isRear();  // false

$drive->eq(DriveWheel::front()); // false
$drive->eq(DriveWheel::rear());  // false
```

## Design

- All constants MUST be private
- All constants MUST be type of integer
- All properties MUST NOT be public
- All properties MUST be static
- Properties MUST contain values for all defined constants
