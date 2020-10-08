<?php

declare(strict_types=1);

namespace Dakujem;

/**
 * A static helper class to compare two floating-point numbers.
 *
 * Both `TwoFloats::same()` and `TwoFloats::compare()` methods use BC Math as default
 * with fallback to native PHP algorithm if BC Math is not available.
 *
 * The native PHP-only computation can be forced using `TwoFloats::sameNative()` and `TwoFloats::compareNative()`.
 * This assumes you do not wish to or can not use BC Math.
 * BC Math allows for arbitrary precision computations, it is probably a safer option.
 *
 * The BC-Math-only computation can also be forced using `TwoFloats::sameBcm()` and `TwoFloats::compareBcm()`.
 *
 * The native PHP algorithm was implemented based on the following resources:
 *
 * Decimal numbers in PHP:
 * @link https://floating-point-gui.de/errors/comparison/
 * @link https://floating-point-gui.de/languages/php/
 *
 * Discussions:
 * @link https://floating-point-gui.de/errors/comparison/
 * @link https://stackoverflow.com/questions/3148937/compare-floats-in-php
 *
 * @author Andrej Rypak <xrypak@gmail.com>
 */
final class TwoFloats
{
    /**
     * Are these two floats "same"?
     * Uses BC Math when available, with native PHP algorithm as fallback.
     *
     * The scale represents precision of computation, i.e. number of fraction digits considered.
     * Numbers between 0 and PHP_FLOAT_DIG (inclusive) are accepted.
     *
     * @param float $a
     * @param float $b
     * @param int|null $scale use NULL (or omit) for maximum precision
     * @return bool
     */
    private static function same(float $a, float $b, int $scale = null): bool
    {
        if (function_exists('bccomp')) {
            // use BC Math by default when available
            return static::sameBcm($a, $b, $scale);
        }

        // fall back to the the native "epsilon" algorithm when BC Math is not available
        return static::sameNative($a, $b, static::epsilonFromScale($scale));
    }

    /**
     * Compare two numbers.
     * Uses BC Math when available, with native PHP algorithm as fallback.
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
     * @param int|null $scale use NULL (or omit) for maximum precision
     * @return int
     */
    private static function compare(float $a, float $b, int $scale = null): int
    {
        if (function_exists('bccomp')) {
            // use BC Math by default when available
            return static::compareBcm($a, $b, $scale);
        }

        // fall back to the the native "epsilon" algorithm when BC Math is not available
        return static::compareNative($a, $b, static::epsilonFromScale($scale));
    }

    /**
     * Are these two floats "same"?
     * Uses BC Math.
     *
     * @param float $a
     * @param float $b
     * @param int|null $scale when NULL is passed, PHP_FLOAT_DIG is used as default for maximum precision
     * @return bool
     */
    public static function sameBcm(float $a, float $b, int $scale = null): bool
    {
        return static::compareBcm($a, $b, $scale) === 0;
    }

    /**
     * Compare two numbers.
     * Uses BC Math.
     *
     * Returns:
     *   1  when the  left operand ($a) is greater
     *  -1  when the right operand ($b) is greater
     *   0  when both operands are equal
     *
     * @param float $a left operand
     * @param float $b right operand
     * @param int|null $scale when NULL is passed, PHP_FLOAT_DIG is used as default for maximum precision
     * @return int
     */
    public static function compareBcm(float $a, float $b, int $scale = null): int
    {
        return bccomp($a, $b, $scale ?? PHP_FLOAT_DIG);
    }

    /**
     * Are these two floats "same"?
     * Uses native PHP implementation.
     *
     * The epsilon represents the maximum accepted relative deviation in the numbers
     * such that the numbers are still considered equal.
     * Defaults to PHP_FLOAT_EPSILON constant for maximum precision.
     * Sometimes it might be desirable to explicitly pass an epsilon for certain comparisons.
     *
     * Algorithm has been taken from the following great resource:
     * @link https://floating-point-gui.de/errors/comparison/
     *
     * @param float $a
     * @param float $b
     * @param float|null $epsilon when NULL is passed, PHP_FLOAT_EPSILON is used as default for maximum precision
     * @return bool returns TRUE if the two numbers are "same", FALSE otherwise
     */
    public static function sameNative(float $a, float $b, float $epsilon = null): bool
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

    /**
     * Compare two numbers.
     * Uses native PHP implementation.
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
    public static function compareNative(float $a, float $b, float $epsilon = null): int
    {
        if (static::sameNative($a, $b, $epsilon)) {
            return 0;
        }
        return $a > $b ? 1 : -1;
    }

    /**
     * Calculate epsilon value for a given scale.
     * "Scale" is used for BC Math calculations, "epsilon" for native calculations.
     *
     * 0                  -->   1
     * 1                  -->   0.1
     * 2                  -->   0.01
     * 3                  -->   0.001
     * 4                  -->   0.0001
     * PHP_FLOAT_DIG      -->   1e-15    *  implementation/hardware specific
     * 42                 -->   1e-42
     *
     * @param int $scale
     * @return float
     */
    public static function epsilonFromScale(int $scale): float
    {
        return $scale === PHP_FLOAT_DIG ? PHP_FLOAT_EPSILON : pow(10, -$scale);
    }

    /**
     * Calculate epsilon value from a scale value.
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
