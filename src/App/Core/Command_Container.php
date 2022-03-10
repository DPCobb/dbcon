<?php
namespace App\Core;

use App\Interfaces\Command_Container_Interface;
use App\Utility\Cleaner;
use App\Utility\Arr;
use stdClass;

class Command_Container implements Command_Container_Interface
{
    /**
     * instance
     *
     * @var array
     */
    public array $instance;

    /**
     * config
     *
     * @var stdClass
     */
    public stdClass $config;

    public function __construct(array $config = [], array $args)
    {
        // Drop the config into a class so we can do config->foo or config->foo[bar] etc
        $this->config = new stdClass();

        foreach ($config as $k => $v) {
            $this->config->$k = $v;
        }

        // Empty instance array
        $this->instance           = [];
        $this->instance['flags']  = [];
        $this->instance['params'] = [];

        $this->processArgs($args);
    }

    /**
     * Processing the command line args passed into the Command Container
     *
     * @param array $args
     *
     * @return void
     */
    public function processArgs(array $args): void
    {
        // Set our command value
        $this->instance['command'] = Cleaner::clean($args[1]);

        // IF we have a sub command
        if (!empty($args[2]) && strpos($args[2], '--') === false && strpos($args[2], '=') === false) {
            // ignore if we are a -fOo flag
            if (!preg_match("/^-\w+/", $args[2])) {
                $this->instance['sub_command'] = Cleaner::clean($args[2]);
            }
        }

        // Process the remaining args for flags and params
        foreach ($args as $k => $v) {
            $this->processFlags($v);

            if (strpos($v, '=') !== false) {
                $params                               = explode('=', $v);
                $this->instance['params'][$params[0]] = $params[1];
            }
        }

        return;
    }

    /**
     * Process an arg value if it is a flag
     *
     * @param string $value
     *
     * @return void
     */
    public function processFlags(string $value): void
    {
        // --foo flags
        if (strpos($value, '--') !== false) {
            $this->instance['flags'][] = str_replace('--', '', $value);
        }

        // -f flags
        if (strpos($value, '-') !== false) {
            $parts = explode('-', $value);

            // ignore dashes in commands, ex: hello-world
            if ($parts[0] !== '') {
                return;
            }

            // Split the flags ex: -iD would be [i, D]
            $flags = str_split($parts[1]);
            // add each as a flag setting
            foreach ($flags as $flag) {
                if (!empty($flag)) {
                    $this->instance['flags'][] = $flag;
                }
            }
        }

        return;
    }

    /**
     * Set a value into the Command_Container instance
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->instance[$key] = $value;
        return;
    }

    /**
     * Get a value from the Command_Container instance, supports dot notation
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Arr::get($key, $this->instance);
    }

    /**
     * Check if the Command_Container instance has a value
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has(string $key): bool
    {
        return !empty(Arr::get($key, $this->instance));
    }

    /**
     * Check if the Command_Container intance has a specific flag
     *
     * @param string $key
     *
     * @return boolean
     */
    public function hasFlag(string $key): bool
    {
        if (empty($this->instance['flags'])) {
            return false;
        }

        return in_array($key, $this->instance['flags']);
    }
}
