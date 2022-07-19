<?php

namespace Openphp\ElasticBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Openphp\ElasticBuilder\CoroutineHandler;
use Openphp\ElasticBuilder\Elastic;

class Connect
{
    /**
     * @var string[]
     */
    public static $hosts = [
        "127.0.0.1:9200",
    ];

    /**
     * @return \Elasticsearch\Client
     */
    public static function client()
    {
        return ClientBuilder::create()
            ->setHosts(static::$hosts)
            ->setSSLVerification(false)->build();
    }

    /**
     * @return \Elasticsearch\Client
     */
    public static function swooleClient()
    {
        return ClientBuilder::create()
            ->setHandler(new CoroutineHandler())
            ->setSSLVerification(false)
            ->setHosts(static::$hosts)
            ->build();
    }

    /**
     * @return \Elasticsearch\Client
     */
    public function clients()
    {
        if (function_exists('swoole_version')
            && class_exists('\Swoole\Coroutine')
            && function_exists('\Swoole\Coroutine::getCid')
            && \Swoole\Coroutine::getCid() > 0) {
            return static::swooleClient();
        }
        return static::client();
    }

    /**
     * @return Elastic|null
     */
    public static function builder()
    {
        return Elastic::getInstance(static::client());
    }
}