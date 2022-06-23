<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

use Openphp\ElasticBuilder\Constant\Opentor;

class Range extends CriteriaAbstract
{
    /**
     * @var string[]
     */
    protected $opentor = [
        Opentor::GT  => 'gt',
        Opentor::GTE => 'gte',
        Opentor::LTE => 'lte',
        Opentor::LT  => 'lt'
    ];

    /**
     * @param array $criteria
     * @return array|\array[][]|\array[][][]
     */
    public function criteriaDsl(array $criteria)
    {
        $operator = $criteria['operator'];
        if ($operator == Opentor::BETWEEN) {
            list($min, $max) = $criteria['value'];
            return $this->between($criteria['field'], $min, $max);
        }
        if ($operator == Opentor::NOT_BETWEEN) {
            list($min, $max) = $criteria['value'];
            return $this->not_between($criteria['field'], $min, $max);
        }
        return $this->range($criteria['field'], $criteria['value'], $this->opentor[$criteria['operator']]);
    }
}