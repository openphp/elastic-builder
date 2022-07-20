<?php

namespace Openphp\ElasticBuilder\Utils;

use ArrayAccess;
use Closure;

class Arr
{
    /**
     * @param $array
     * @param $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * @param $array
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public static function get($array, $key, $default = null)
    {
        if ($key instanceof Closure) {
            return $key($array, $default);
        }
        if (!static::accessible($array)) {
            return $default instanceof Closure ? $default() : $default;
        }
        if (is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        if (strpos($key, '.') === false) {
            return isset($array[$key]) ? $array[$key] : ($default instanceof Closure ? $default() : $default);
        }
        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }
        return $array;
    }

    /**
     * @param array $array
     * @param $key
     * @param $groups
     * @return array|mixed
     */
    public static function index(array $array, $key, $groups = [])
    {
        $results = [];
        $groups  = (array)$groups;
        foreach ($array as $element) {
            $lastArray = &$results;
            foreach ($groups as $group) {
                $value = static::get($element, $group);
                if (!array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }
            if ($key === null) {
                if (!empty($groups)) {
                    $lastArray[] = $element;
                }
            } else {
                $value = static::get($element, $key);
                if ($value !== null) {
                    if (is_float($value)) {
                        $value = str_replace(',', '.', (string)$value);
                    }
                    $lastArray[$value][] = $element;
                }
            }
            unset($lastArray);
        }
        return $results;
    }

    /**
     * @param ...$arrays
     * @return array
     */
    public static function merge(...$arrays)
    {
        $rest = [];
        foreach ($arrays as $arr) {
            foreach ($arr as $key => $val) {
                if (empty($rest[$key])) {
                    $rest[$key] = $val;
                } else {
                    $rest[$key] = array_merge($val, $rest[$key]);
                }
            }
        }
        return $rest;
    }
}