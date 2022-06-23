<?php

namespace Openphp\ElasticBuilder\Elastic;

trait QueryDSL
{
    /**
     * @var array
     */
    protected $query = [];


    /**
     * @return $this
     */
    public function clearQuery()
    {
        $this->query = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $path
     * @param array $query
     * @return $this
     */
    public function nested($path, array $query = [])
    {
        if (empty($query)) {
            $query = [
                'match_all' => new \stdClass()
            ];
        }
        $this->query['bool']['must'][] = [
            'nested' => [
                'path'  => $path,
                'query' => $query
            ]
        ];
        return $this;
    }

    /**
     * @param $path
     * @param array $query
     * @return $this
     */
    public function nestedNot($path, array $query = [])
    {
        if (empty($query)) {
            $query = [
                'match_all' => new \stdClass()
            ];
        }
        $this->query['bool']['must_not'][] = [
            'nested' => [
                'path'  => $path,
                'query' => $query
            ]
        ];
        return $this;
    }

    /**
     * @param $filed
     * @param $value
     * @return $this
     */
    public function where($filed, $value = null)
    {
        if (is_null($value)) {
            if (is_array($filed)) {
                foreach ($filed as $f => $v) {
                    $this->query['bool']['must'][] = [
                        'match_phrase' => [
                            $f => $v
                        ]
                    ];
                }
            }
        } else {
            $this->query['bool']['must'][] = [
                'match_phrase' => [
                    $filed => $value
                ]
            ];
        }
        return $this;
    }

    /**
     * @param $filed
     * @param $value
     * @return $this
     */
    public function whereShould($filed, $value = null)
    {
        if (is_null($value)) {
            if (is_array($filed)) {
                foreach ($filed as $f => $v) {
                    $this->query['bool']['should'][] = [
                        'match_phrase' => [
                            $f => $v
                        ]
                    ];
                }
            }
        } else {
            $this->query['bool']['should'][] = [
                'match_phrase' => [
                    $filed => $value
                ]
            ];
        }
        return $this;
    }

    /**
     * @param $filed
     * @param $value
     * @return $this
     */
    public function whereNot($filed, $value = null)
    {
        if (is_null($value)) {
            if (is_array($filed)) {
                foreach ($filed as $f => $v) {
                    $this->query['bool']['must_not'][] = [
                        'match_phrase' => [
                            $f => $v
                        ]
                    ];
                }
            }
        } else {
            $this->query['bool']['must_not'][] = [
                'match_phrase' => [
                    $filed => $value
                ]
            ];
        }
        return $this;
    }

    /**
     * @param $filed
     * @param array $value
     * @return $this
     */
    public function whereIn($filed, array $value)
    {
        $this->query['bool']['must'][] = [
            'terms' => [
                $filed => $value
            ]
        ];
        return $this;
    }

    /**
     * @param $filed
     * @param array $value
     * @return $this
     */
    public function whereNotIn($filed, array $value)
    {
        $this->query['bool']['must_not'][] = [
            'terms' => [
                $filed => $value
            ]
        ];
        return $this;
    }

    /**
     * @param $field
     * @param $gte
     * @param $lte
     * @return $this
     */
    public function whereRange($field, $gte = null, $lte = null)
    {
        $range = [
            'range' => [
                $field => []
            ]
        ];
        if (!is_null($gte)) {
            $range['range'][$field]['gte'] = $gte;
        }
        if (!is_null($lte)) {
            $range['range'][$field]['lte'] = $lte;
        }
        $this->query['bool']['must'][] = $range;
        return $this;
    }

    /**
     * @param $field
     * @param $gte
     * @param $lte
     * @return $this
     */
    public function whereNotRange($field, $gte = null, $lte = null)
    {
        $range = [
            'range' => [
                $field => []
            ]
        ];
        if (!is_null($gte)) {
            $range['range'][$field]['gte'] = $gte;
        }
        if (!is_null($lte)) {
            $range['range'][$field]['lte'] = $lte;
        }
        $this->query['bool']['must_not'][] = $range;
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereExists($field)
    {
        $this->query['bool']['must'][] = [
            'exists' => [
                'field' => $field
            ]
        ];
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereNotExists($field)
    {
        $this->query['bool']['must_not'][] = [
            'exists' => [
                'field' => $field
            ]
        ];
        return $this;
    }

    /**
     * @param $query
     * @param array $fields
     * @return $this
     */
    public function whereQueryString($query, array $fields = [])
    {
        $condition = ['query_string' => ['query' => $query]];
        if (!empty($fields)) {
            $condition['query_string']['fields'] = $fields;
        }
        $this->query['bool']['must'][] = $condition;
        return $this;
    }

    /**
     * @param $query
     * @param array $fields
     * @return $this
     */
    public function whereNotQueryString($query, array $fields = [])
    {
        $condition = ['query_string' => ['query' => $query]];
        if (!empty($fields)) {
            $condition['query_string']['fields'] = $fields;
        }
        $this->query['bool']['must_not'][] = $condition;
        return $this;
    }

    /**
     * @param $script
     * @return $this
     */
    public function whereScript($script)
    {

        $this->query['bool']['must'][] = [
            'script' => [
                "script" => $script
            ]
        ];
        return $this;
    }

    /**
     * @param $script
     * @return $this
     */
    public function whereNotScript($script)
    {

        $this->query['bool']['must_not'][] = [
            'script' => [
                "script" => $script
            ]
        ];
        return $this;
    }
}