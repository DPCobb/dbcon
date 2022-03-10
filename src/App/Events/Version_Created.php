<?php

namespace App\Events;

use App\Interfaces\Event_Provider_Interface;
use App\IO\Output;
use App\Utility\Stored_Settings;

class Version_Created implements Event_Provider_Interface
{
    
    /**
     * settings
     *
     * @var Stored_Settings
     */
    public Stored_Settings $settings;

    public function __construct()
    {
        $this->settings = new Stored_Settings;
    }

    /**
     * Run the event
     *
     * @param string $event_name
     * @param array $data
     *
     * @return void
     */
    public function run(string $event_name, array $data = [])
    {
        if (empty($data['file'])) {
            Output::error('File path not present in data');
        }

        $settings = $this->settings->getSettings();

        $settings['version_files'][$data['file']] = false;

        $this->settings->save($settings);
    }
}
