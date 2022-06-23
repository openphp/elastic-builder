<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

class Defaults extends CriteriaAbstract
{

    public function criteriaDsl(array $criteria)
    {
        $field = explode('|', $criteria['field']);
        $value = $criteria['value'];
        if (is_array($value) && count($field) == 1) {
            // fileld 是字符串 value 是数组
            return $this->terms($field[0], $value);
        } else {
            $valDSL = $this->match_phrase($field[0], $value);
        }
        if (count($field) != 1) {
            // 字段多个,值是1个表示是or类型需要用should
            //  field|field2|field3
            $valDSL = $this->match_phrase_should($field, $value);
        }
        return [
            'bool' => [$this->mustOrMustNot($criteria['operator']) => $valDSL]
        ];
    }
}