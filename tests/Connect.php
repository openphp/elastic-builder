<?php

namespace Openphp\ElasticBuilder\Tests;

use Elasticsearch\ClientBuilder;
use Openphp\ElasticBuilder\Elastic;

class Connect
{
    /**
     * @return \Elasticsearch\Client
     */
    public static function client()
    {
        return ClientBuilder::create()->setHosts([
            "127.0.0.1:9200",
        ])->setSSLVerification(false)->build();
    }


    /**
     * @return Elastic|null
     */
    public static function builder()
    {
        return Elastic::getInstance(static::client());
    }
}