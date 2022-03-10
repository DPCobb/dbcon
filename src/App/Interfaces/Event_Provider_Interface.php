<?php
namespace App\Interfaces;

interface Event_Provider_Interface
{
    public function run(string $event_name, array $data = []);
}
