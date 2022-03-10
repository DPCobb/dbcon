<?php

namespace App\Utility;

final class Arr
{
    public static $instance;

    public function __construct(array $arr)
    {
        $this->array = $arr;
    }

    /**
     * Gets the value from a dot notation key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function read(string $key)
    {
        return Arr::get($key, $this->array);
    }

    /**
     * Initialize an instance of Arr
     *
     * @param array $data
     *
     * @return Arr
     */
    public static function init(array $data) : Arr
    {
        static::$instance = new Arr($data);

        return static::$instance;
    }

    /**
     * Gets a value from an array using dot notation
     *
     * @param string $key
     * @param array $array
     *
     * @return mixed
     */
    public static function get(string $key, array $array)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            while (count($keys) > 0) {
                $k = array_shift($keys);

                if (is_null($array)) {
                    return null;
                }

                if (!array_key_exists($k, $array)) {
                    $array[$k] = null;
                }
                $array = $array[$k];
            }

            return $array;
        }

        return null;
    }
}
