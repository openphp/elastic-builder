<?php

namespace Openphp\ElasticBuilder\Elastic;

trait Indices
{
    /**
     * 删除索引
     * @return bool
     */
    public function drop()
    {

        $result = $this->client->indices()->delete([
            'index' => $this->index
        ]);
        return $result['acknowledged'] ?: false;
    }

    /**
     * 获取表结构
     * @return array
     */
    public function desc()
    {
        return $this->client->indices()->getMapping([
            'index' => $this->index,
            'type'  => $this->type
        ]);
    }

    /**
     * 判断索引是否存在
     * @return bool
     */
    public function existIndex()
    {
        return $this->client->indices()->exists([
            'index' => $this->index
        ]);
    }


    /**
     * 创建索引
     * @param array $settings ["number_of_shards" => 1],
     * @param array $properties ['auth' => ['type' => 'nested']]
     * @return array
     */
    public function createIndex($settings = [], $properties = [])
    {
        $params['index'] = $this->index;
        if (!empty($settings)) {
            $params['body']['settings'] = $settings;
        }
        if (!empty($properties)) {
            $type                       = $this->type;
            $params['body']['mappings'] = [
                $type => [
                    "_source"    => [
                        'enabled' => true
                    ],
                    'properties' => $properties
                ]
            ];
        }
        return $this->client->indices()->create($params);
    }


    /**
     * 创建模版
     * @param string $templateName table-temp
     * @param array $settings ["number_of_shards" => 1,]
     * @param array $properties ['auth' => ['type' => 'nested']]
     * @return array
     */
    public function putTemplateAll($templateName, $settings, $properties)
    {
        $aliases  = [
            $templateName . '-all' => new \stdClass()
        ];
        $mappings = [
            'doc' => [
                "_source"    => [
                    'enabled' => true
                ],
                'properties' => $properties
            ]
        ];
        return $this->putTemplate($templateName, $settings, $mappings, $aliases, $templateName . '-*');
    }

    /**
     * @param string $templateName
     * @param array $settings
     * @param array $mappings
     * @param array $aliases
     * @param string $index_patterns
     * @return array
     */
    public function putTemplate($templateName, $settings = [], $mappings = [], $aliases = [], $index_patterns = '')
    {
        $params['name']             = $templateName;
        $params['body']['template'] = $templateName . '*';
        if (!empty($aliases)) {
            $params['body']['aliases'] = $aliases;
        }
        if ($index_patterns) {
            $params['body']['index_patterns'] = $index_patterns;
        }
        if (!empty($settings)) {
            $params['body']['settings'] = $settings;
        }
        if (!empty($mappings)) {
            $params['body']['mappings'] = $mappings;
        }
        return $this->client->indices()->putTemplate($params);
    }

    /**
     * 查看模版
     * @param $name
     * @return array
     */
    public function getTemplate($name)
    {
        $params = [
            'name' => $name,
        ];
        return $this->client->indices()->getTemplate($params);
    }

    /**
     * 删除模版
     * @param $name
     * @return array
     */
    public function deleteTemplate($name)
    {
        $params = [
            'name' => $name,
        ];
        return $this->client->indices()->deleteTemplate($params);
    }
}