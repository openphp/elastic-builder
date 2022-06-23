<?php

namespace Openphp\ElasticBuilder\Utils;

class Number
{
    /**
     * 百分比字符串
     * @param string $num1
     * @param string $num2
     * @return string
     */
    public static function percentageString($num1, $num2)
    {
        return static::percentage($num1, $num2) . '%';
    }

    /**
     * 百分比
     * @param string $num1
     * @param string $num2
     * @param int $scale
     * @return string
     */
    public static function percentage($num1, $num2, $scale = 2)
    {
        return static::bcmul(static::bcdiv($num1, $num2, 6), 100, $scale);
    }

    /**
     * bcdiv — 2个任意精度的数字除法计算
     * @param string $left_operand
     * @param string $right_operand
     * @param int $scale
     * @return int|string|null
     */
    public static function bcdiv($left_operand, $right_operand, $scale = 2)
    {
        if ($right_operand && $left_operand) {
            return bcdiv($left_operand, $right_operand, $scale);
        }
        return 0;
    }

    /**
     * bcmul — 2个任意精度数字乘法计算
     * @param string $num1
     * @param string $num2
     * @param int $scale
     * @return int|string
     */
    public static function bcmul($num1, $num2, $scale = 0)
    {
        if ($num1 && $num2) {
            return bcmul($num1, $num2, $scale);
        }
        return 0;
    }
}