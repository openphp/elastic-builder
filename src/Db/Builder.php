<?php

namespace Openphp\ElasticBuilder\Db;

use Openphp\ElasticBuilder\Elastic;

class Builder
{
    use Elastic\ElasticBase, Criteria, Stats;

    /**
     * @var Elastic
     */
    protected $elastic;

    /**
     * @var array
     */
    protected $body = [];

    /**
     * @param Elastic $elastic
     */
    public function __construct(Elastic $elastic)
    {
        $this->elastic = $elastic;
        $this->setClient();
    }

    /**
     * @return void
     */
    protected function setClient()
    {
        $this->client = $this->elastic->getClient();
    }

    /**
     * @param $index
     * @return $this
     */
    public function index($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    const UNIT_S  = 's';
    const UNIT_MS = 'ms';
    const UNIT_M  = 'm';

    /**
     * @param int $timeout
     * @param string $unit
     * @return $this
     */
    public function timeout($timeout, $unit = Builder::UNIT_MS)
    {
        $this->timeout($timeout . $unit);
        return $this;
    }

    /**
     * @param array $aggs
     * @return $this
     */
    public function aggs(array $aggs)
    {
        $this->body['aggs'] = $aggs;
        return $this;
    }

    /**
     *  指定查询字段
     * @param string|array $_source
     * @return $this
     */
    public function source($_source = true)
    {
        if (is_string($_source)) {
            $_source = array_map('trim', explode(',', $_source));
        }
        if ($_source === true) {
            $_source = "*";
        }
        $this->body['_source'] = is_array($_source) ? array_unique($_source) : "*";
        return $this;
    }

    /**
     * @param string|array $field
     * @param string $order
     * @return $this
     */
    public function order($field, $order = 'asc')
    {
        static $sorts = [];
        if (is_string($field)) {
            $sorts[$field] = [
                'order' => $order
            ];
        }
        if (is_array($field)) {
            foreach ($field as $f => $o) {
                $sorts[$f] = ['order' => $o];
            }
        }
        $this->body['sort'] = $sorts;
        return $this;
    }

    /**
     * 指定查询数量
     * @param int $length 查询数量
     * @param int|null $offset 起始位置
     * @return $this
     */
    public function limit($length, $offset = null)
    {
        $this->size($length);
        if (!is_null($offset)) {
            $this->from($offset);
        }
        return $this;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function size($size)
    {
        $this->body['size'] = $size;
        return $this;
    }

    /**
     * @param int $from
     * @return $this
     */
    public function from($from)
    {
        $this->body['from'] = $from;
        return $this;
    }

    /**
     * @return void
     */
    public function setParams()
    {
        $this->params['index']         = $this->index;
        $this->params['type']          = $this->type;
        $this->params['body']          = $this->body;
        $this->params['body']['query'] = $this->getQuery();
    }

    /**
     * @return array
     */
    public function search()
    {
        return $this->officialSearch();
    }
}