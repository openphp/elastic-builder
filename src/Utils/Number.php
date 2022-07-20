<?php

namespace Openphp\ElasticBuilder\Utils;

class Number
{
    /**
     * @param $n
     * @return bool
     */
    public static function is_number($n)
    {
        if (is_int($n) || is_long($n) || is_float($n) || is_double($n))
            return true;
        return false;
    }

    /**
     * @param $numerator
     * @param $denominator
     * @param $precision
     * @return float|int
     */
    public static function percent($numerator, $denominator, $precision = 2)
    {
        if (empty($denominator) || empty($numerator) || !static::is_number($numerator) || !static::is_number($denominator)) {
            return 0;
        }
        return round($numerator * 100 / $denominator, $precision);
    }

    /**
     * @param $numerator
     * @param $denominator
     * @param $precision
     * @return string
     */
    public static function percentString($numerator, $denominator, $precision = 2)
    {
        return static::percent($numerator, $denominator, $precision) . '%';
    }

    /**
     * @param $n
     * @param $d
     * @param $precision
     * @return float|int
     */
    public static function division($n, $d, $precision = 2)
    {
        if (empty($n) || empty($d) || !static::is_number($n) || !static::is_number($d)) {
            return 0;
        }
        return round($n / $d, $precision);
    }

    /**
     * @param $new
     * @param $old
     * @param $precision
     * @return float|int
     */
    public static function increase($new, $old, $precision = 2)
    {
        return static::percent($new - $old, $old, $precision);
    }
}