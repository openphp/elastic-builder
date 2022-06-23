<?php

namespace Openphp\ElasticBuilder\Searcher;

use Openphp\ElasticBuilder\Indicator;
use Openphp\ElasticBuilder\Subject;
use Openphp\ElasticBuilder\Utils\Arr;

abstract class Searcher
{
    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var array
     */
    protected $searchResult = [];
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @var Indicator[]
     */
    protected $indicators;
    /**
     * @var array
     */
    protected $aggs = [];

    /**
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $this->subject    = $subject;
        $this->params     = $this->subject->getParams();
        $this->indicators = $this->getAllIndicators($this->subject->indicators);
    }

    /**
     * 加载所有的指标
     * @param Indicator[] $indicators
     * @return array
     */
    protected function getAllIndicators(array $indicators)
    {
        $allIndicators = [];
        foreach ($indicators as $indicator) {
            $allIndicators[] = $indicator;
            if ($relates = $indicator::$relate) {
                /**@var Indicator[] $relates */
                foreach ($relates as $relate) {
                    if (is_subclass_of($relate, Indicator::class)) {
                        $allIndicators[] = new $relate($this->subject->request);
                    }
                }
            }
        }
        return $allIndicators;
    }

    /**
     * @return void
     */
    abstract protected function buildAggs();

    /**
     * @return void
     */
    abstract public function result();

    /**
     * @return array
     */
    public function params()
    {
        $this->buildAggs();
        if ($aggs = $this->aggs) {
            $this->params['body']['aggs'] = $aggs;
        }
        return $this->params;
    }

    /**
     * @return $this
     */
    public function search()
    {
        $params             = $this->params();
        $this->searchResult = $this->subject->officialSearch($params);
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }


    /**
     * @param array $endPushAggs
     * @param array $resetFields
     * @return array|mixed
     */
    protected function fieldsAggs(array $endPushAggs, $resetFields = [])
    {
        if (empty($resetFields)) {
            return $endPushAggs;
        }
        $fields = array_values($resetFields);
        //找到最后一个字段
        $maxDepth = max(array_keys($fields));
        $aggs     = &$result;
        foreach ($fields as $level => $field) {
            $tempLevel = $level;
            while ($level--) {
                if (!isset($aggs[key($aggs)]['aggs'])) {
                    $aggs[key($aggs)]['aggs'] = [];
                }
                $aggs = &$aggs[key($aggs)]['aggs'];
            }
            $aggs[$field['field']]['terms'] = $field;
            if ($tempLevel === $maxDepth && !empty($endPushAggs)) {
                $aggs[$field['field']]['aggs'] = $endPushAggs;
            }
            unset($tempLevel);
            $aggs = &$result;
        }
        return $aggs;
    }


    /**
     * @param array $aggregations
     * @param array $result
     * @param array $fields
     * @return void
     */
    protected function recursionResult(array $aggregations, &$result, array $fields)
    {
        foreach ($aggregations['buckets'] as $aggregation) {
            $endResult = [];
            if (isset($aggregation['key']) && $intersectFields = array_intersect(array_keys($aggregation), $fields)) {
                $field = reset($intersectFields);
                foreach ($aggregation[$field]['buckets'] as $item) {
                    $endResult[$item['key']] = [];
                }
                $this->recursionResult($aggregation[$field], $endResult, $fields);
            } else {
                $endResult = $this->indirectsResult($aggregation);
            }
            $key          = isset($aggregation['key_as_string']) ? $aggregation['key_as_string'] : $aggregation['key'];
            $result[$key] = $endResult;
        }
    }

    /**
     * @param $aggregations
     * @return array
     */
    protected function indirectsResult($aggregations)
    {
        $result = [];
        foreach ($aggregations as $name => $aggregation) {
            if (isset($aggregation['value'])) {
                $result[$name] = $aggregation['value'];
            } else {
                foreach ($aggregation as $ks => $val) {
                    if ($ks == 'doc_count') {
                        $result[$name . '_doc_count'] = $val;
                    } else {
                        foreach ($val as $k => $v) {
                            if ($k == 'value') {
                                $result[$name] = $val['value'];
                            } elseif ($k == 'doc_count') {
                                $result[$name.'_'.$ks.'_doc_count'] = $v;
                            } else {
                                $result[$k] = isset($v['value']) ? $v['value'] : $v;
                            }
                        }
                    }
                }
            }
        }
        foreach ($this->indicators as $indicator) {
            if (is_array($rest = $indicator->result($result))) {
                $result = array_merge($rest, $result);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function indicatorsAggs()
    {
        $crc32indirects = Arr::index($this->indicators, function (Indicator $indirect) {
            return $indirect::$mainName . '_' . crc32(json_encode($indirect->mainFilter()));
        });
        if (empty($crc32indirects)) {
            return [];
        }
        $returnAggs = [];
        foreach ($crc32indirects as $indirects) {
            /**@var Indicator[] $indirects */
            foreach ($indirects as $mainIndirect) {
                $mainName = $mainIndirect::$mainName;
                $name     = $mainIndirect::$name;
                if ($mainName && $mainFilter = $mainIndirect->mainFilter()) {
                    $index = $mainName.'_'.crc32(json_encode($mainIndirect->mainFilter()));
                    $returnAggs[$index]['filter'] = $mainFilter;
                    if ($filter = $mainIndirect->filter()) {
                        $returnAggs[$index]['aggs'][$name]['filter']      = $filter;
                        $returnAggs[$index]['aggs'][$name]['aggs'][$name] = $this->indicatorBaseAggs($mainIndirect);
                    } else {
                        $returnAggs[$index]['aggs'][$name] = $this->indicatorBaseAggs($mainIndirect);
                    }
                } else {
                    if ($filter = $mainIndirect->filter()) {
                        $returnAggs[$name]['filter']      = $filter;
                        $returnAggs[$name]['aggs'][$name] = $this->indicatorBaseAggs($mainIndirect);
                    } else {
                        $returnAggs[$name] = $this->indicatorBaseAggs($mainIndirect);
                    }
                }
            }
        }
        return $returnAggs;
    }


    /**
     * @param Indicator $indirect
     * @return array[]
     */
    protected function indicatorBaseAggs(Indicator $indirect)
    {
        return [$indirect::$agg_type => ['field' => $indirect::$field]];
    }
}