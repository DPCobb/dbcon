<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Utility\Cleaner;

class CleanerTest extends TestCase
{
    public function testCleanWithNoFilter()
    {
        $res = Cleaner::clean('test@example.com<>');

        $this->assertEquals($res, 'test@example.com');
    }

    public function testCleanWithFilter()
    {
        $res = Cleaner::clean('test@example.com<>', FILTER_SANITIZE_URL);

        $this->assertEquals($res, 'test@example.com<>');
    }
}
