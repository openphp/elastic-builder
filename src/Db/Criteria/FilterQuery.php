<?php

namespace Openphp\ElasticBuilder\Db\Criteria;

use Openphp\ElasticBuilder\Constant\Opentor;
use Openphp\ElasticBuilder\Db\Criteria\Filter\CriteriaAbstract;
use Openphp\ElasticBuilder\Db\Criteria\Filter\Defaults;
use Openphp\ElasticBuilder\Db\Criteria\Filter\In;
use Openphp\ElasticBuilder\Db\Criteria\Filter\Is;
use Openphp\ElasticBuilder\Db\Criteria\Filter\Like;
use Openphp\ElasticBuilder\Db\Criteria\Filter\Range;
use Openphp\ElasticBuilder\Db\Criteria\Filter\Strings;
use Openphp\ElasticBuilder\Utils\Arr;

class FilterQuery
{
    use DbQueryDSL;

    /**
     * @var
     */
    protected $criteria;

    /**
     * @var array
     */
    protected $factor = [
        'range'   => [
            Opentor::LT,
            Opentor::LTE,
            Opentor::GT,
            Opentor::GTE,
            Opentor::BETWEEN,
            Opentor::NOT_BETWEEN
        ],
        'is'      => [
            Opentor::IS,
            Opentor::IS_NOT,
        ],
        'in'      => [
            Opentor::IN,
            Opentor::NOT_IN
        ],
        'like'    => [
            Opentor::LIKE,
            Opentor::NOT_LIKE
        ],
        'strings' => [
            Opentor::NOT_STRING,
            Opentor::STRING,
        ]
    ];
    /**
     * @var array
     */
    protected $classMap = [
        'range'   => Range::class,
        'default' => Defaults::class,
        'is'      => Is::class,
        'in'      => In::class,
        'like'    => Like::class,
        'strings' => Strings::class
    ];


    /**
     * @param $criteria
     */
    public function __construct($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param string $factor
     * @param array $fieldValues
     * @return CriteriaAbstract
     */
    protected function getNewClass($factor, array $fieldValues)
    {
        $class = $this->classMap[$factor];
        return new $class($fieldValues);
    }

    /**
     * @return array|\stdClass
     */
    public function getDSL()
    {
        $criteria = $this->criteria;
        if (empty($criteria)) {
            return $this->match_all();
        }
        $newCriteria = Arr::index($criteria, function ($e) {
            foreach ($this->factor as $classIndex => $factor) {
                if (in_array($e['operator'], $factor)) {
                    return $classIndex;
                }
            }
            return 'default';
        });
        foreach ($newCriteria as $factor => $fieldValues) {
            $filter[] = $this->getNewClass($factor, $fieldValues)->getDSL();
        }
        return ['bool' => [
            'filter' => $filter
        ]];
    }
}