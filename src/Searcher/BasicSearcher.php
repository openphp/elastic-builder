<?php

namespace Openphp\ElasticBuilder\Searcher;


class BasicSearcher extends Searcher
{
    /**
     * @return void
     */
    protected function buildAggs()
    {
        $this->aggs = $this->indicatorsAggs();
    }

    /**
     * @return array
     */
    public function result()
    {
        $aggregations = $this->searchResult['aggregations'];
        return $this->indirectsResult($aggregations);
    }
}