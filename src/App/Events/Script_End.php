<?php

namespace App\Events;

use App\Interfaces\Event_Provider_Interface;
use App\IO\Output;

class Script_End implements Event_Provider_Interface
{
    public function run(string $event_name, array $data = [])
    {
        $time = date("Y-m-d H:i:s");
        $memory = (memory_get_usage() / 1024) /1024;

        Output::success("Completed at $time, memory used $memory MB");
    }
}
