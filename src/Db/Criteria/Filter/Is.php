<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

use Openphp\ElasticBuilder\Constant\Opentor;

class Is extends CriteriaAbstract
{

    /**
     * @param array $criteria
     * @return array|array[]
     */
    public function criteriaDsl(array $criteria)
    {
        //不存在
        if ($criteria['operator'] == Opentor::IS_NOT) {
            return $this->exists($criteria['field']);
        }
        //存在
        $valDSL = $this->exists($criteria['field']);
        return [
            'bool' => [$this->mustOrMustNot($criteria['operator']) => $valDSL]
        ];
    }
}