<?php

declare(strict_types=1);

namespace Dakujem;

/**
 * A static helper class to compare two floating-point numbers.
 *
 * Usage:
 *   To discover whether two float numbers are same, or for comparison, these calls can be used:
 *   TwoFloats::same($a, $b);            // true or false
 *   TwoFloats::compare($a, $b) === 0;   // or >0 or <0
 *
 *   To limit the precision to certain number of fraction digits, use:
 *   TwoFloats::same($a, $b, TwoFloats::epsilon(2));  // with precision of 2 fraction digits
 *
 * The native numeric PHP algorithm with relative epsilon was implemented based on the following resources:
 * @link https://floating-point-gui.de/errors/comparison/
 * @link https://floating-point-gui.de/languages/php/
 *
 * Discussions related to floating-point comparisons:
 * @link https://stackoverflow.com/questions/3148937/compare-floats-in-php
 * @link https://floating-point-gui.de/errors/comparison/
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TwoFloats
{
    /**
     * Are these two floats "same"?
     *
     * When epsilon is null, maximum achievable precision is used, thus the two numbers can be considered same,
     * as far as native numeric calculations go.
     *
     * In other cases, epsilon represents the minimum deviation
     * such that the two numbers are considered different.
     *
     * Epsilon can be calculated from the desired number of fraction digits using
     * `TwoFloats::scaleToEpsilon`, e.g. `TwoFloats::scaleToEpsilon(4)` for 4-digit precision.
     *
     * @param float $a
     * @param float $b
     * @param float|null $epsilon use NULL (or omit) for maximum precision
     * @return bool
     */
    public static function same(float $a, float $b, float $epsilon = null): bool
    {
        if ($epsilon === null) {
            // use the maximum precision available to the platform
            return static::sameRelative($a, $b);
        }

        // use fixed epsilon for better convenience in other cases
        return static::equal($a, $b, $epsilon);
    }

    /**
     * Compare two numbers.
     *
     * When epsilon is null, maximum achievable precision is used, thus the two numbers can be considered same,
     * as far as native numeric calculations go.
     *
     * In other cases, epsilon represents the minimum deviation
     * such that the two numbers are considered different.
     *
     * Returns:
     *   1  when the  left operand ($a) is greater
     *  -1  when the right operand ($b) is greater
     *   0  when both operands are equal
     *
     * The scale represents precision of computation, i.e. number of fraction digits considered.
     * Numbers between 0 and PHP_FLOAT_DIG (inclusive) are accepted.
     *
     * @param float $a left operand
     * @param float $b right operand
     * @param float|null $epsilon use NULL (or omit) for maximum precision
     * @return int
     */
    public static function compare(float $a, float $b, float $epsilon = null): int
    {
        if ($epsilon === null) {
            // use the maximum precision available to the platform
            return static::compareRelative($a, $b);
        }

        // use fixed epsilon for better convenience in other cases
        return static::compareEqual($a, $b, $epsilon);
    }

    /**
     * Uses a naive numeric PHP implementation with absolute epsilon value.
     *
     * The epsilon represents the minimum absolute deviation in the numbers
     * such that the numbers are considered different.
     * It might be desirable to explicitly pass an epsilon to limit the precision to certain number of fraction digits.
     * `TwoFloats::scaleToEpsilon` method can be used to calculate epsilon from the number of fraction digits.
     *
     * @param float $a
     * @param float $b
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default for maximum precision
     * @return bool returns TRUE if the two numbers are equal with the given tolerance
     */
    public static function equal(float $a, float $b, float $epsilon = null): bool
    {
        if ($a === $b) {
            return true;
        }
        // TODO this is not adequate for big numbers !
        return abs($a - $b) < abs($epsilon ?? PHP_FLOAT_EPSILON);
    }

    /**
     * Compare two numbers.
     * Uses a naive numeric PHP implementation with absolute epsilon value.
     *
     * Returns:
     *   1  when the  left operand ($a) is greater
     *  -1  when the right operand ($b) is greater
     *   0  when both operands are equal
     *
     * @param float $a left operand
     * @param float $b right operand
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default for maximum precision
     * @return int
     */
    public static function compareEqual(float $a, float $b, float $epsilon = null): int
    {
        if (static::equal($a, $b, $epsilon)) {
            return 0;
        }
        return $a > $b ? 1 : -1;
    }

    /**
     * Are these two floats "same"?
     * Uses numeric PHP implementation with relative epsilon.
     *
     * This algorithm should yield the maximum possible precision using native numeric calculations
     * when used with the default PHP_FLOAT_EPSILON epsilon value.
     *
     * The epsilon represents the minimum relative deviation in the numbers
     * such that the numbers are considered different.
     * Defaults to PHP_FLOAT_EPSILON constant for maximum precision.
     * Most of the times the default should not be overridden for this method;
     * for cases where the precision needs to be limited on purpose,
     * `TwoFloats::equal` method might be a better fit.
     * See the docs for more info.
     *
     * @param float $a
     * @param float $b
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default for maximum precision
     * @return bool returns TRUE if the two numbers are "same", FALSE otherwise
     */
    public static function sameRelative(float $a, float $b, float $epsilon = null): bool
    {
        if ($a === $b) {
            return true;
        }
        $diff = abs($a - $b);
        if ($a === 0.0 || $b === 0.0 || (abs($a) + abs($b) < PHP_FLOAT_MIN)) {
            return $diff < (abs($epsilon ?? PHP_FLOAT_EPSILON) * PHP_FLOAT_MIN);
        }
        return $diff / min(abs($a) + abs($b), PHP_FLOAT_MAX) < abs($epsilon ?? PHP_FLOAT_EPSILON);
    }

    /**
     * Compare two numbers.
     * Uses numeric PHP implementation with relative epsilon.
     *
     * Returns:
     *   1  when the  left operand ($a) is greater
     *  -1  when the right operand ($b) is greater
     *   0  when both operands are equal
     *
     * @param float $a left operand
     * @param float $b right operand
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default for maximum precision
     * @return int
     */
    public static function compareRelative(float $a, float $b, float $epsilon = null): int
    {
        if (static::sameRelative($a, $b, $epsilon)) {
            return 0;
        }
        return $a > $b ? 1 : -1;
    }

    /**
     * Calculate epsilon value for a given scale.
     * "Scale" is the number of decimals after which the deviation is tolerated.
     * "Epsilon" is the maximum tolerated deviation.
     *
     * 0                  -->   1
     * 1                  -->   0.1
     * 2                  -->   0.01
     * 3                  -->   0.001
     * 4                  -->   0.0001
     * PHP_FLOAT_DIG      -->   1e-15    *  implementation/hardware specific
     * 42                 -->   1e-42
     *
     * @param int $scale number of fraction digits / precision
     * @return float
     */
    public static function epsilonFromScale(int $scale = PHP_FLOAT_DIG): float
    {
        return $scale === PHP_FLOAT_DIG ? PHP_FLOAT_EPSILON : pow(10, -$scale);
    }

    /**
     * Shorthand call for `epsilonFromScale`.
     *
     * @param int $scale number of fraction digits / precision
     * @return float
     */
    public static function epsilon(int $scale = PHP_FLOAT_DIG): float
    {
        return static::epsilonFromScale($scale);
    }

    /**
     * Calculate scale value from an epsilon.
     * The inverse of `epsilonFromScale()`.
     *
     * 0                  -->   0
     * 1                  -->   0
     * 0.1                -->   1
     * 0.01               -->   2
     * 0.001              -->   3
     * 0.0001             -->   4
     * PHP_FLOAT_EPSILON  -->  15    *  implementation/hardware specific
     * 1e-42              -->  42
     *
     * Warning, this method is not fool-proof:
     * 100                -->  -2
     *
     * @param float $epsilon
     * @return int
     */
    public static function scaleFromEpsilon(float $epsilon): int
    {
        return $epsilon === PHP_FLOAT_EPSILON ? PHP_FLOAT_DIG : -(int)floor(log($epsilon, 10));
    }
}
