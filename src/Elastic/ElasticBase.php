<?php

namespace Openphp\ElasticBuilder\Elastic;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;

trait ElasticBase
{
    use Indices;

    /**
     * @var string
     */
    public $index;
    /**
     * @var string
     */
    public $type = 'doc';

    /**
     * @var Client
     */
    public $client = null;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @return void
     */
    abstract protected function setClient();

    /**
     * @return void
     */
    abstract protected function setParams();

    /**
     * @return array
     */
    public function getParams()
    {
        $this->setParams();
        return $this->params;
    }

    /**
     * @return array
     */
    public function officialSearch($params = [])
    {
        return $this->client->search(array_merge($this->getParams(), $params));
    }

    /**
     * explain评分分析
     * @param bool $explain
     * @return $this
     */
    public function explain($explain)
    {
        $this->params['explain'] = $explain;
        return $this;
    }

    /**
     * 在timeout时间范围内返回部分数据(可能是全部数据)
     * @param string $timeout
     * @return $this
     */
    public function timeout($timeout)
    {
        $this->params['timeout'] = $timeout;
        return $this;
    }

    /**
     * @param bool $version
     * @return $this
     */
    public function version($version)
    {
        $this->params['version'] = $version;
        return $this;
    }


    /**
     *  通过条件删除数据
     * @return int
     */
    public function delete()
    {
        $this->setParams();
        $result = $this->client->deleteByQuery($this->params);
        return $result['deleted'];
    }

    /**
     * 插入或者更新
     * @param array $data
     * @return bool
     */
    public function update(array $data)
    {
        $result = $this->client->update([
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => [
                'doc'           => $data,
                'doc_as_upsert' => true,
            ]
        ]);
        return !empty($result['_id']);
    }

    /**
     * 批量插入
     * @param array $row
     * @param string $_index
     * @param string $_type
     * @return bool
     */
    public function bulk(array $row, $_index = '', $_type = 'doc')
    {
        if (empty($row)) {
            return false;
        }
        $params = [];
        foreach ($row as $item) {
            $params['body'][] = [
                'update' => [
                    '_id'    => $item['id'],
                    '_index' => $_index ?: $this->index,
                    '_type'  => $_type ?: $this->type
                ]
            ];
            $params['body'][] = [
                'doc'           => $item,
                'doc_as_upsert' => true
            ];
        }
        $result = $this->client->bulk($params);
        return $result['errors'] === false && count($result['items']) == count($row);
    }

    /**
     * 根据id进行删除
     * @param string|int $id
     * @return bool
     */
    public function deleteById($id)
    {
        try {
            $result = $this->client->delete([
                'index' => $this->index,
                'type'  => $this->type,
                'id'    => $id,
            ]);
            return $result['found'] == true && $result['result'] == 'deleted';
        } catch (Missing404Exception $e) {
            return true;
        }
    }
}