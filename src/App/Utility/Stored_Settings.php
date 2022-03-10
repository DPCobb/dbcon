<?php

namespace App\Utility;

class Stored_Settings
{
    public function getSettings(): array
    {
        if (!file_exists(ROOT . "/dbcon.json")) {
            return [];
        }

        $data = file_get_contents(ROOT . "/dbcon.json");
        return json_decode($data, true);
    }

    public function save(array $data): void
    {
        if (!file_exists(ROOT . "/dbcon.json")) {
            return;
        }

        $data = json_encode($data);
        file_put_contents(ROOT . "/dbcon.json", $data);
        return;
    }
}
