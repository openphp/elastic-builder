<?php

namespace Openphp\ElasticBuilder\Searcher;

class TermsSearcher extends Searcher
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        foreach ($fields as $field => &$parameter) {
            if (is_array($parameter)) {
                $parameter['field'] = $field;
            }
            if (is_string($parameter)) {
                $parameter = ['field' => $parameter];
            }
        }
        $this->fields = $fields;
        return $this;
    }

    /**
     * @return void
     */
    protected function buildAggs()
    {
        $this->aggs = $this->fieldsAggs($this->indicatorsAggs(), $this->fields);
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = $this->searchResult['aggregations'];
        $fields       = $this->fields ? array_column($this->fields, 'field') : [];
        $result       = [];
        $this->recursionResult($aggregations[reset($fields)], $result, $fields);
        return $result;
    }
}