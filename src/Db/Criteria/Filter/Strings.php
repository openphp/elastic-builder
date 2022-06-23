<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

class Strings extends CriteriaAbstract
{

    public function criteriaDsl(array $criteria)
    {
        // queryString field为_all value="a=1 or b=2"
        $valDSL = $this->query_string($criteria['value'], $criteria['field']);
        return [
            'bool' => [$this->mustOrMustNot($criteria['operator']) => $valDSL]
        ];
    }
}