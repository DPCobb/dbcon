<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Config;

class ConfigTest extends TestCase
{
    public function testConfigSetsBools()
    {
        $config = new Config;

        $config->config = [
            'foo' => 'true',
            'bar' => 'abc',
            'baz' => [
                'foo' => 'false',
                'bar' => '123'
            ]
        ];

        $res = $config->setBools($config->config);

        $this->assertTrue($res['foo']);
        $this->assertFalse($res['baz']['foo']);
        $this->assertEquals($res['bar'], 'abc');
        $this->assertEquals($res['baz']['bar'], '123');
    }
}
