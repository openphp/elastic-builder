<?php

namespace Openphp\ElasticBuilder\Utils;

class Timer
{
    /**
     * @param $time
     * @return int
     */
    public function quarter($time)
    {
        $m = date('n', $time);
        if ($m >= 1 && $m <= 3) {
            $q = 1;
        } else if ($m >= 4 && $m <= 6) {
            $q = 2;
        } else if ($m >= 7 && $m <= 9) {
            $q = 3;
        } else if ($m >= 10 && $m <= 12) {
            $q = 4;
        } else {
            $q = 0;
        }
        return $q;
    }
}