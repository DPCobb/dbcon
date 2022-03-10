<?php

namespace App\Core;

use App\Interfaces\Command_Container_Interface;
use App\IO\Output;

class Command_Validator
{

    /**
     * command_container
     *
     * @var Command_Container_Interface
     */
    public Command_Container_Interface $Command_Container;

    public function __construct(Command_Container_Interface $command_container)
    {
        $this->Command_Container = $command_container;
    }

    /**
     * Ensure any passed flags or parameters are allowed
     *
     * @param string $type
     *
     * @param array $allowed
     * @return void
     */
    public function processPassedArgs(string $type, array $allowed): void
    {
        if (empty($allowed)) {
            return;
        }
        $sent = $this->Command_Container->get($type);

        foreach ($sent as $k => $v) {
            if ($type !== 'flags') {
                if (!in_array($k, $allowed)) {
                    Output::multi(['ERROR!!!!!', 'Unknown parameter: ' . $k, "Allowed parameters are: " . implode(', ', $allowed)], 'error');
                    exit;
                }
                continue;
            }

            if (!in_array($v, $allowed)) {
                Output::multi(['ERROR!!!!!', 'Unknown flag: ' . $v, "Allowed flags are: " . implode(', ', $allowed)], 'error');
                exit;
            }
        }
    }

    /**
     * Check that any required params are present
     *
     * @param array $required
     *
     * @return void
     */
    public function checkRequiredParams(array $required): void
    {
        if (empty($required)) {
            return;
        }

        $sent = $this->Command_Container->get('params');

        foreach ($required as $key) {
            if (!array_key_exists($key, $sent)) {
                Output::multi(['ERROR!!!!!', "Missing required parameter: $key", "Parameters sent: " . implode(', ', $sent)], 'error');
                exit;
            }
        }
        return;
    }
}
