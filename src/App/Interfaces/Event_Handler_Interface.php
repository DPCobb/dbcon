<?php
namespace App\Interfaces;

use App\Interfaces\Event_Provider_Interface;

interface Event_Handler_Interface
{
    public function dispatch(string $event, array $data = []): void;
    public function subscribe(string $event, Event_Provider_Interface $action): void;
}
