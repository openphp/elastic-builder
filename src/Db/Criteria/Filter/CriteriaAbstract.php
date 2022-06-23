<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

use Openphp\ElasticBuilder\Constant\Opentor;
use Openphp\ElasticBuilder\Db\Criteria\DbQueryDSL;

abstract class CriteriaAbstract
{
    use DbQueryDSL;

    /**
     * @var array
     */
    protected $criteria;

    /**
     * @param $criteria
     */
    public function __construct($criteria)
    {
        $this->criteria = $criteria;
    }


    /**
     * @param array $ofvs
     * @return array
     */
    abstract public function criteriaDsl(array $criteria);

    /**
     * @param string $operator
     * @return string
     */
    public function mustOrMustNot($operator)
    {
        $m = 'must';
        if ($operator == Opentor::NE
            || $operator == Opentor::NOT_LIKE
            || $operator == Opentor::IS
            || $operator == Opentor::NOT_STRING
        ) {
            $m = 'must_not';
        }
        return $m;
    }

    /**
     * @return array
     */
    public function getDSL()
    {
        $factorFv = $this->criteria;
        $dsl      = [];
        foreach ($factorFv as $ofvs) {
            $dsl[] = $this->criteriaDsl($ofvs);
        }
        return $dsl;
    }
}