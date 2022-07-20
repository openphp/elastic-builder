<?php

namespace Openphp\ElasticBuilder;

use Openphp\ElasticBuilder\Searcher\BasicSearcher;
use Openphp\ElasticBuilder\Searcher\TermsSearcher;
use Openphp\ElasticBuilder\Elastic\ElasticBase;
use Openphp\ElasticBuilder\Elastic\QueryDSL;
use Openphp\ElasticBuilder\Searcher\TimeSearcher;

//  定义主题基础类
//   一个主题就是一个Elastic索引
//  1.重写setClient方法进行连接Elasticsearch
//  2.定义index 索引名称（索引别名）
//  3.如果需要全局需要进行过滤条件,可以如下操作
//  public function __construct(){
//     $this->where('sex',1);
//     parent::__construct();
//  }
//
abstract class Subject
{
    use QueryDSL, ElasticBase;

    /**
     * @var int
     */
    public $size = 0;
    /**
     * @var Indicator[]
     */
    public $indicators;
    /**
     * @var array
     */
    public $request = [];

    /**
     * @return void;
     */
    public function __construct()
    {
        $this->setClient();
    }

    /**
     * @return mixed
     */
    abstract protected function setClient();

//    /**
//     * @return void
//     */
//    protected function setClient()
//    {
//        $this->client = \Elasticsearch\ClientBuilder::create()->setHosts([
//            "127.0.0.1:9200",
//        ])->setSSLVerification(false)->build();
//    }

    /**
     * @return void
     */
    protected function setParams()
    {
        $this->params['index']        = $this->index;
        $this->params['type']         = $this->type;
        $this->params['body']['size'] = $this->size;
        if ($query = $this->getQuery()) {
            $this->params['body']['query'] = $query;
        }
    }

    /**
     * @param array $aggs
     * @return $this
     */
    public function aggs(array $aggs)
    {
        $this->params['body']['aggs'] = $aggs;
        return $this;
    }

    /**
     * @return array
     */
    public function search()
    {
        return $this->officialSearch();
    }

    /**
     * @param string|array $indicators
     * @param array $request
     * @return  $this
     */
    public function setIndicators($indicators, array $request = [])
    {
        $this->request = array_merge($this->request, $request);
        $indicators    = (array)$indicators;
        array_walk($indicators, function ($indicator) {
            $this->setIndicator(new $indicator($this->request));
        });
        return $this;
    }

    /**
     * @param $gte
     * @param $lte
     * @return $this
     */
    public function setMainRange($gte = null, $lte = null)
    {
        $this->request['elastic_builder_main_name_gte'] = $gte;
        $this->request['elastic_builder_main_name_lte'] = $lte;
        return $this;
    }

    /**
     * @param Indicator $indicator
     * @return $this
     */
    protected function setIndicator(Indicator $indicator)
    {
        $this->indicators[crc32(get_class($indicator))] = $indicator;
        return $this;
    }

    /**
     * @return BasicSearcher
     */
    public function searchBasicData()
    {
        $this->getParams();
        return (new BasicSearcher($this))
            ->search();
    }

    /**
     * @param string $field
     * @param int $size
     * @return TermsSearcher
     */
    public function searchSingleTerms($field, $size = 20)
    {
        return $this->searchTerms([$field => ['size' => $size]]);
    }

    /**
     * @param array $fields
     * ['name'=>['size'=>2],'sex'=>['size'=>1]]
     * ['name','sex']
     * ['name','sex'=>['size'=>1]]
     * @return TermsSearcher
     */
    public function searchTerms(array $fields)
    {
        $this->getParams();
        return (new TermsSearcher($this))
            ->setFields($fields)
            ->search();
    }

    /**
     * year（1y）年
     * quarter（1q）季度
     * month（1M）月份
     * week（1w）星期
     * day（1d）天
     * hour（1h）小时
     * minute（1m）分钟
     * second（1s）秒
     * @param $field
     * @param $interval
     * @param $min_doc_count
     * @return TimeSearcher
     */
    public function searchDateHistogram($field, $interval = '1d', $min_doc_count = 0)
    {
        return $this->searchTime($field, [
            'date_histogram' => [
                'field'         => $field,
                'interval'      => $interval,
                'time_zone'     => date_default_timezone_get(),
                'min_doc_count' => $min_doc_count,
                'order'         => [
                    '_key' => 'desc',
                ]
            ]
        ]);
    }

    /**
     * @param string $name group_by_create_time
     * @param array $date
     *  [
     *    'date_histogram' => [
     *        'field' => 'create_time',
     *        'interval'=>'quarter',
     *        'format'=>'yyyy-MM-dd',
     *     ]
     *  ]
     * @param array $fields
     * ['name'=>['size'=>2],'sex'=>['size'=>1]]
     * ['name','sex']
     * ['name','sex'=>['size'=>1]]
     * @return TimeSearcher
     */
    public function searchTime($name, array $date, array $fields = [])
    {
        $this->getParams();
        return (new TimeSearcher($this))
            ->date($name, $date)
            ->setFields($fields)->search();
    }
}