<?php

namespace App\Core;

class Config
{
    /**
     * path
     *
     * @var string
     */
    public string $path;

    /**
     * config
     *
     * @var array
     */
    public array $config;

    /**
     * config
     *
     * @var Config
     */
    public static $instance;

    /**
     * Load and parse the config file, setting the config array
     *
     * @param string $path
     *
     * @return void
     */
    public function set(string $path): void
    {
        if (file_exists($path)) {
            $this->config = parse_ini_file($path, true);
            // is this a Unit Test?
            $this->config['is_unit'] = $this->areWeUnit();
            // set the bool strings to an actual bool value
            $this->config = $this->setBools($this->config);
        }

        return;
    }

    /**
     * Set strings to bool value
     *
     * @param array $config
     *
     * @return array
     */
    public function setBools(array $config): array
    {
        foreach ($config as $k => &$v) {
            if (is_array($v)) {
                $v = $this->setBools($v, true);
                continue;
            }

            $value = strtolower($v);

            if ($value === 'false' || $value === 'true') {
                $v = $value === 'true' ? true : false;
            }
        }

        return $config;
    }

    /**
     * Get the config array
     *
     * @return array
     */
    public function get():array
    {
        return $this->config;
    }

    /**
     * Load the config class and set the config value
     *
     * @param string $path
     *
     * @return self
     */
    public static function load(string $path)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config;
        }

        self::$instance->set($path);

        return self::$instance;
    }

    /**
     * Check if this is running in a Unit Test
     *
     * @return boolean
     */
    public function areWeUnit() : bool
    {
        if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
            return false;
        }

        return true;
    }
}
