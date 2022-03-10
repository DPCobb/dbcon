<?php

namespace App\Core;

use App\Interfaces\Event_Handler_Interface;
use App\Interfaces\Event_Provider_Interface;
use Error;

/**
 * A really basic event handler
 */
class Event_Handler implements Event_Handler_Interface
{
    public function __construct()
    {
        $this->events = [];
    }

    /**
     * Subscribe to events with a callable action
     *
     * @param string $event
     * @param Event_Provider_Interface $action
     *
     * @return void
     */
    public function subscribe(string $event, Event_Provider_Interface $action, string $method = ''): void
    {
        if (!is_object($action)) {
            throw new Error('Given action is not an object!');
        }

        if (!method_exists($action, 'run')) {
            throw new Error("$action is missing the run method!!");
        }

        if (empty($event)) {
            return;
        }

        if (empty($method)) {
            $this->events[$event][] = $action;
            return;
        }

        $this->events[$event][] = ['class' => $action, 'method' => $method];
    }

    /**
     * Dispatch events
     *
     * @param string $event
     *
     * @return void
     */
    public function dispatch(string $event, array $data = []): void
    {
        if (empty($event)) {
            return;
        }

        $listeners = $this->events[$event];

        foreach ($listeners as $k => $action) {
            if (is_array($action)) {
                $this->runNamedMethodEvent($action, $event, $data);
                continue;
            }
            call_user_func_array([$action, 'run'], [$event, $data]);
            continue;
        }
    }

    /**
     * Run an event with method other than the default run method from interface
     *
     * @param array $action
     *
     * @return void
     */
    public function runNamedMethodEvent(array $action, string $event, array $data): void
    {
        if (method_exists($action['class'], $action['method'])) {
            call_user_func_array([$action['class'], $action['method']], [$event, $data]);
        }
    }
}
