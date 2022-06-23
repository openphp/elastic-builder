<?php

namespace Openphp\ElasticBuilder;

use Elasticsearch\Client;
use Openphp\ElasticBuilder\Db\Builder;

class Elastic
{
    /**
     * @var Client
     */
    protected static $client = null;

    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        static::$client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return static::$client;
    }

    /**
     * sql builder 搜索，采用的是filter搜索方式
     * @param $index
     * @return Builder
     */
    public function index($index)
    {
        return (new Builder($this))->index($index);
    }

    /**
     * @return bool
     */
    public function ping()
    {
        return $this->getClient()->ping();
    }

    /**
     * Elastic服务器基本信息
     * @return array
     */
    public function info()
    {
        return $this->getClient()->info();
    }

    /**
     * Elastic节点信息
     * @return array
     */
    public function nodes()
    {
        return $this->getClient()->cat()->nodes();
    }

    /**
     * Elastic所有的索引
     * @return array
     */
    public function indices()
    {
        return $this->getClient()->cat()->indices();
    }


    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @param mixed ...$args
     * @return static
     */
    public static function getInstance(...$args)
    {
        if (!isset(static::$instance)) {
            static::$instance = new static(...$args);
        }
        return static::$instance;
    }
}