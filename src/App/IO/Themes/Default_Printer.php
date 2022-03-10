<?php

namespace App\IO\Themes;

use App\Interfaces\Printer_Interface;

class Default_Printer implements Printer_Interface
{
    public function getThemeSettings() : array
    {
        return [
            'error' => '1;37;41',
            'warning' => '0;33',
            'alert' => '0;31',
            'message' => '0;37',
            'info' => '1;34',
            'success' => '0;32',
            'banner' => '1;37;45',
            'caption' => '0;90',
        ];
    }
}
