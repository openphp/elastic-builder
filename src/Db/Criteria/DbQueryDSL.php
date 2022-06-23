<?php

namespace Openphp\ElasticBuilder\Db\Criteria;


trait DbQueryDSL
{
    /**
     * @param $field
     * @return array[]
     */
    protected function exists($field)
    {
        return [
            'exists' => [
                'field' => $field
            ]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @return array[]
     */
    protected function term($field, $value)
    {
        return [
            'term' => [
                $field => $value
            ]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @return array[]
     */
    protected function terms($field, $value)
    {
        return [
            'terms' => [
                $field => $value
            ]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @return array[]
     */
    protected function match($field, $value)
    {
        return [
            'match' => [
                $field => $value
            ]
        ];
    }

    /**
     * @return \stdClass[]
     */
    protected function match_all()
    {
        return [
            'match_all' => new \stdClass()
        ];
    }


    /**
     * @param string $value
     * @param array $field
     * @return array|array[]
     */
    protected function query_string($value, array $field = [])
    {
        $condition = ['query_string' => ['query' => $value]];
        if (!empty($field)) {
            $condition['query_string']['fields'] = $field;
        }
        return $condition;
    }

    /**
     * @param $field
     * @param $value
     * @return \array[][]
     */
    protected function match_phrase($field, $value)
    {
        return [
            'match_phrase' => [
                $field => [
                    "query" => $value
                ]
            ]
        ];
    }

    /**
     * @param $fields
     * @param $value
     * @return \array[][]
     */
    protected function match_phrase_should($fields, $value)
    {
        $should = [];
        foreach ($fields as $field) {
            $should[] = [
                'match_phrase' => [
                    $field => [
                        "query" => $value
                    ]
                ]
            ];
        }
        return ['bool' => ['should' => $should]];
    }

    /**
     * @param $field
     * @param $value
     * @return array[]
     */
    protected function wildcard($field, $value)
    {
        return [
            'wildcard' => [
                $field => $value
            ]
        ];
    }

    /**
     * @param array $fields
     * @param $value
     * @return \array[][]
     */
    protected function wildcard_should(array $fields, $value)
    {
        $should = [];
        foreach ($fields as $field) {
            $should[] = [
                'wildcard' => [
                    $field => $value
                ]
            ];
        }
        return ['bool' => ['should' => $should]];
    }

    /**
     * @param $field
     * @param $min
     * @param $max
     * @return \array[][]
     */
    protected function between($field, $min, $max)
    {
        return [
            'range' => [
                $field => [
                    'gte' => $min,
                    'lte' => $max,
                ]
            ]
        ];
    }

    /**
     * @param $field
     * @param $min
     * @param $max
     * @return \array[][][]
     */
    protected function not_between($field, $min, $max)
    {
        return [
            'range' => [
                'NOT' => [
                    $field => [
                        'gte' => $min,
                        'lte' => $max
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @param $opentor
     * @return \array[][]
     */
    protected function range($field, $value, $opentor)
    {
        return [
            'range' => [
                $field => [
                    $opentor => $value
                ]
            ]
        ];
    }

    /**
     * @param $field
     * @param $value
     * @param $opentor
     * @return array[]
     */
    protected function range_should($field, $value, $opentor)
    {
        return [
            'bool' => [
                'should' => $this->range($field, $value, $opentor)
            ]
        ];
    }
}