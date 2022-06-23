<?php

namespace Openphp\ElasticBuilder\Tests;

use PHPUnit\Framework\TestCase;

class ElasticTest extends TestCase
{
    public function testBase()
    {
        $this->assertIsArray(Connect::builder()->info());
        $this->assertIsArray(Connect::builder()->indices());
        $this->assertIsArray(Connect::builder()->nodes());
    }


    public function testCURD()
    {
        $this->assertIsBool(Connect::builder()->index('table_bulk')->bulk([[
            'id'   => 1,
            'name' => 'zhiqiang'
        ], [
            'id'   => 2,
            'name' => 'Openphp'
        ]]));
        $this->assertIsBool(Connect::builder()->index('table')->update([
            'id'       => 1,
            'name'     => 'Openphp',
            'password' => '123456',
        ]));
        $this->assertIsInt(Connect::builder()->index('table_bulk')->where('id', 1)->delete());
        $s = Connect::builder()->index('table')->where('id', 1)->officialSearch();
        $this->assertIsArray($s);
    }

    public function testIndex()
    {
        $this->assertIsBool(Connect::builder()->index('table')->existIndex());
        $this->assertIsArray(Connect::builder()->index('table')->desc());
        $this->assertIsBool(Connect::builder()->index('table')->drop());
    }
}