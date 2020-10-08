<?php

declare(strict_types=1);

namespace Dakujem;

/**
 * A static helper class to compare two floating-point numbers for equality.
 *
 * This assumes you do not wish to or can not use BCMath.
 * BCMath allows for arbitrary precision computations.
 *
 * Note:
 *    Using [BCMath](https://www.php.net/manual/en/book.bc.php) might still be a better option.
 *
 * Decimal numbers in PHP:
 * @link https://floating-point-gui.de/languages/php/
 *
 * Discussions:
 * @link https://floating-point-gui.de/errors/comparison/
 * @link https://stackoverflow.com/questions/3148937/compare-floats-in-php
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
class TwoFloats
{
    /**
     * Are these two floats "same"?
     *
     * The epsilon represents the maximum accepted relative deviation in the numbers
     * such that the numbers are still considered same.
     * Defaults to PHP_FLOAT_EPSILON constant.
     * Sometimes it might be desirable to explicitly pass an epsilon for certain comparisons.
     *
     * Algorithm has been taken from the following great resource:
     * @link https://floating-point-gui.de/errors/comparison/
     *
     * @param float $a
     * @param float $b
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default
     * @return bool returns TRUE if the two numbers are "same", FALSE otherwise
     */
    public static function same(float $a, float $b, float $epsilon = null): bool
    {
        if ($a === $b) {
            return true;
        }
        $diff = abs($a - $b);
        if ($a === 0.0 || $b === 0.0 || (abs($a) + abs($b) < PHP_FLOAT_MIN)) {
            return $diff < (($epsilon ?? PHP_FLOAT_EPSILON) * PHP_FLOAT_MIN);
        }
        return $diff / min(abs($a) + abs($b), PHP_FLOAT_MAX) < ($epsilon ?? PHP_FLOAT_EPSILON);
    }
}
