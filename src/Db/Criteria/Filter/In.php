<?php

namespace Openphp\ElasticBuilder\Db\Criteria\Filter;

use Openphp\ElasticBuilder\Constant\Opentor;

class In extends CriteriaAbstract
{

    /**
     * @param array $criteria
     * @return array|array[]
     */
    public function criteriaDsl(array $criteria)
    {
        $field = $criteria['field'];
        $value = $criteria['value'];
        if ($criteria['operator'] == Opentor::IN || (is_array($criteria['field']) && Opentor::EQ)) {
            return $this->term_or_terms($field, $value);
        }
        return [
            'bool' => [$this->mustOrMustNot($criteria['operator']) => $this->term_or_terms($field, $value)]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @return array[]
     */
    public function term_or_terms($field, $value)
    {
        if (is_array($value)) {
            return $this->terms($field, $value);
        } else {
            return $this->term($field, $value);
        }
    }
}