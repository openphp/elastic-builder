<?php

namespace Openphp\ElasticBuilder\Db;

use Openphp\ElasticBuilder\Constant\Opentor;
use Openphp\ElasticBuilder\Db\Criteria\FilterQuery;

trait Criteria
{
    /**
     * @var array
     */
    protected $criteria = [];

    /**
     * @return array
     */
    protected function getQuery()
    {
        return (new FilterQuery($this->criteria))->getDSL();
    }

    /**
     * @param array $criteria
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @param string $connector
     * @return $this
     */
    private function criteria(array &$criteria, $field, $value, $operator = Opentor::EQ, $connector = 'AND')
    {
        $criteria[crc32(serialize(func_get_args()))] = [
            'field'     => $field,
            'value'     => $value,
            'operator'  => strtoupper($operator),
            'connector' => strtoupper($connector),
        ];
        return $this;
    }

    /**
     * 指定AND查询条件
     * @access public
     * @param mixed $field
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function where($field, $value, $operator = Opentor::EQ)
    {
        $this->criteria($this->criteria, $field, $value, $operator);
        return $this;
    }

    /**
     * @param string $value
     * @param array $field
     * @return $this
     */
    public function whereQueryString($value, array $field = [])
    {
        $this->where($field, $value, Opentor::STRING);
        return $this;
    }

    /**
     * @param string $value
     * @param array $field
     * @return $this
     */
    public function whereNotQueryString($value, array $field = [])
    {
        $this->where($field, $value, Opentor::NOT_STRING);
        return $this;
    }

    /**
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereIn($field, array $value)
    {
        $this->where($field, $value, Opentor::IN);
        return $this;
    }

    /**
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereNotIn($field, array $value)
    {
        $this->where($field, $value, Opentor::NOT_IN);
        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereLike($field, $value)
    {
        $this->where($field, $value, Opentor::LIKE);
        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereNotLike($field, $value)
    {
        $this->where($field, $value, Opentor::NOT_LIKE);
        return $this;
    }

    /**
     * where and 语句
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function andWhere($field, $value, $operator = Opentor::EQ)
    {
        $this->where($field, $value, $operator);
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function whereIsNull($field)
    {
        $this->where($field, null, Opentor::IS);
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function whereIsNotNull($field)
    {
        $this->where($field, null, Opentor::IS_NOT);
        return $this;
    }

    /**
     * between语句
     * @param string $field
     * @param mixed $min
     * @param mixed $max
     * @return $this
     */
    public function whereBetween($field, $min, $max)
    {
        $this->where($field, [$min, $max], Opentor::BETWEEN);
        return $this;
    }

    /**
     * not between语句
     * @param string $field
     * @param mixed $min
     * @param mixed $max
     * @return $this
     */
    public function whereNotBetween($field, $min, $max)
    {
        $this->where($field, [$min, $max], Opentor::NOT_BETWEEN);
        return $this;
    }
}