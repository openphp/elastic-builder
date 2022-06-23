<?php

namespace Openphp\ElasticBuilder\Db;

trait Stats
{
    /**
     * @param string $field
     * @return array|\ArrayAccess|mixed
     */
    public function count($field = 'id')
    {
        $stats = $this->stats($field);
        return isset($stats['aggregations'][$field]['count'])
            ? $stats['aggregations'][$field]['count']
            : 0;
    }


    /**
     * @param string $field
     * @return array|\ArrayAccess|mixed
     */
    public function max($field = 'id')
    {
        $stats = $this->stats($field);
        return isset($stats['aggregations'][$field]['max'])
            ? $stats['aggregations'][$field]['max']
            : 0;
    }


    /**
     * @param string $field
     * @return array|\ArrayAccess|mixed
     */
    public function min($field = 'id')
    {
        $stats = $this->stats($field);
        return isset($stats['aggregations'][$field]['min'])
            ? $stats['aggregations'][$field]['min']
            : 0;
    }


    /**
     * @param string $field
     * @return array|\ArrayAccess|mixed
     */
    public function avg($field = 'id')
    {
        $stats = $this->stats($field);
        return isset($stats['aggregations'][$field]['avg'])
            ? $stats['aggregations'][$field]['avg']
            : 0;
    }


    /**
     * @param string $field
     * @return array|\ArrayAccess|mixed
     */
    public function sum($field = 'id')
    {
        $stats = $this->stats($field);
        return isset($stats['aggregations'][$field]['sum'])
            ? $stats['aggregations'][$field]['sum']
            : 0;
    }

    /**
     * 返回统计值。min，max，sum，count，avg
     * @param string $field
     * @return array
     */
    public function stats($field)
    {
        //查询数据显示0条
        $this->size(0);
        $this->aggs([
            $field => [
                'stats' => [
                    'field' => $field,
                ]
            ]
        ]);
        return $this->officialSearch();
    }

    /**
     * 在stats的基础上加了方差标准差等
     * @param string $field
     * @return array
     */
    public function extendedStats($field)
    {
        //查询数据显示0条
        $this->size(0);
        $this->aggs([
            $field => [
                'extended_stats' => [
                    'field' => $field,
                ]
            ]
        ]);
        return $this->officialSearch();
    }

    /**
     * 百分位数统计
     * @param $field
     * @param array $percents
     * @return array
     */
    public function percentiles($field, array $percents = [1, 5, 25, 50, 75, 95, 99])
    {
        $this->size(0);
        $this->aggs([
            $field => [
                'percentiles' => [
                    'field'    => $field,
                    'percents' => $percents
                ]
            ]
        ]);
        return $this->officialSearch();
    }

    /**
     * 字段的数值所占的百分位
     * @param $field
     * @param $values
     * @return array
     */
    public function percentileRanks($field, $values = [10, 15])
    {
        $this->size(0);
        $this->aggs([
            $field => [
                'percentile_ranks' => [
                    'field'    => $field,
                    'percents' => $values
                ]
            ]
        ]);
        return $this->officialSearch();
    }
}