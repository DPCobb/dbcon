<?php

namespace App\IO\Themes;

use App\Interfaces\Printer_Interface;

/**
 * Custom Printer Example
 */
class Alt_Printer implements Printer_Interface
{
    public function getThemeSettings() : array
    {
        // Adding entries here allows for additional Output calls.
        return [
            'error' => '0;36;41',
            'warning' => '0;37',
            'alert' => '0;31',
            'message' => '0;37',
            'info' => '1;31',
            'success' => '0;32',
            'banner' => '1;37;45',
            'shout' => '1;37;46' // this would be Output->shout('Some Message');
        ];
    }
}
