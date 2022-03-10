<?php

namespace App\Utility;

class Cleaner
{
    public static function clean($value, $filter = null)
    {
        return filter_var($value, $filter ?: FILTER_SANITIZE_STRING);
    }
}
