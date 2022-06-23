<?php

namespace Openphp\ElasticBuilder;

use Openphp\ElasticBuilder\Constant\AggType;
use Openphp\ElasticBuilder\Elastic\QueryDSL;


// 定义指标基础类
// 一个指标一个数结果值
// filter 使用说明
// 1 先使用 $this->clearQuery();清空数据
// 2 使用 $this->getQuery() 或 $this->query进行返回即可
// 3 使用nested方法的时候并且还要使用QueryBuilder中的方法，可以这么操作
//public function getFilter()
//{
//    $this->clearQuery();
//    $this->where('auth.user_id', '44');
//    $this->where('auth.bind_flag', '1');
//    $nestedQuery = $this->getQuery();
//    $this->clearQuery();
//    $this->nested('auth', $nestedQuery);
//    return $this->getQuery();
//}
//
// A指标和B指标定义mainName都是create_at,表示的是A和B基于create_at范围内在进行统计，
//   如果需要A在create_at需要在时间范围内进行统计，B 不需要在create_at时间范围内统计，需要重写mainFilter方法进行特定条件筛选或返回空数组
//
// result方法使用说明
// result接受到当当前所有指标当数值，你可以拿到这些数值进行计算，比如返回某率
//public function result($result)
//{
//    return [
//        'lv1' => $result[A::name()] / $result[B::name()],
//        'lv2' => $result[A::name()] / $result[B::name()]
//    ];
//}
abstract class Indicator
{
    use QueryDSL;

    /**
     * @var string
     */
    public static $mainName = '';

    /**
     * @var string
     */
    public static $name = 'id_count';

    /**
     * @var string
     */
    public static $field = 'id';

    /**
     * @var string
     */
    public static $agg_type = AggType::COUNT;

    /**
     * @var array
     */
    public static $relate = [];

    /**
     * @var array
     */
    public $request = [];

    /**
     * @param array $request
     */
    public function __construct($request = [])
    {
        $this->request = $request;
    }


    /**
     * @param array $result
     * @return array []
     */
    public function result(array $result)
    {
        return [];
    }

    /**
     * @return array
     */
    public function mainFilter()
    {
        $this->clearQuery();
        if (isset($this->request['elastic_builder_main_name_gte']) && isset($this->request['elastic_builder_main_name_lte'])) {
            $this->whereRange(static::$mainName, $this->request['elastic_builder_main_name_gte'], $this->request['elastic_builder_main_name_lte']);
        }
        return $this->getQuery();
    }

    /**
     * @return array
     */
    public function filter()
    {
        return [];
    }
}