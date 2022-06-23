<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

class Like extends CriteriaAbstract
{

    /**
     * @param array $criteria
     * @return array[]
     */
    public function criteriaDsl(array $criteria)
    {
        $field = explode('|', $criteria['field']);
        if (count($field) != 1) {
            $valDSL = $this->wildcard_should($field, $criteria['value']);
        } else {
            //like搜索
            $valDSL = $this->wildcard($field[0], $criteria['value']);
        }
        return [
            'bool' => [$this->mustOrMustNot($criteria['operator']) => $valDSL]
        ];
    }
}