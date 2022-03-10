<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Command_Container;

/**
 * Tests the main Application Class
 */
class ApplicationTest extends TestCase
{
    /**
     * Set up our "mock" class
     *
     * @return void
     */
    protected function getApp()
    {
        return new class extends Application {
            public function __construct()
            {
                // "Mock" Command_Container
                $cc = new class([], []) extends Command_Container {
                    public function processArgs(array $ags): void
                    {
                        return;
                    }
                };
                parent::__construct($cc);
            }
        };
    }
    /**
     * Tests the command parser that turns dashed-commands to their Class_Name
     *
     * @return void
     */
    public function testParseCommandRemovesDashes()
    {
        // Build a "mock" Application class...
        $app = $this->getApp();

        $res = $app->parseCommand('test-parse');
        $this->assertEquals('Test_Parse', $res);

        $res = $app->parseCommand('test');
        $this->assertEquals('Test', $res);
    }

    /**
     * Make sure the set method is working
     *
     * @return void
     */
    public function testActionsAreSet()
    {
        $app = $this->getApp();

        $app->set('test', 'Test\Class@test');

        $res = $app->commands;

        $this->assertTrue(array_key_exists('test', $res));
        $this->assertTrue(in_array('Test\Class@test', array_values($res)));
    }

    /**
     * Makes sure the Alias method is setting aliases
     *
     * @return void
     */
    public function testAliasesGetSet()
    {
        $app = $this->getApp();

        $app->alias(['test', 't'], 'Test\Class@test');

        $res = $app->commands;

        $this->assertTrue(array_key_exists('test', $res));
        $this->assertTrue(array_key_exists('t', $res));
        $this->assertTrue(in_array('Test\Class@test', array_values($res)));
    }
}
