<?php
namespace App\Core;

use App\Interfaces\Command_Container_Interface;
use App\IO\Output;
use App\Core\Command_Validator;
use Error;

class Application
{
    /**
     * command_path
     *
     * @var string
     */
    public string $command_path;

    /**
     * command_class
     *
     * @var string
     */
    public string $command_class;

    /**
     * commands
     *
     * @var array
     */
    public array $commands;

    /**
     * Command_Container
     *
     * @var Command_Container_Interface
     */
    public Command_Container_Interface $Command_Container;

    /**
     * Command_Validator
     *
     * @var Command_Validator
     */
    public Command_Validator $Command_Validator;

    /**
     * instance
     *
     * @var Application|null
     */
    public static ?Application $instance = null;

    /**
     * Event
     *
     * @var Event_Handler
     */
    public Event_Handler $Event;

    public function __construct(Command_Container_Interface $Command_Container)
    {
        $this->Command_Container = $Command_Container;
        $this->Command_Validator = new Command_Validator($Command_Container);
        $this->command_path      = 'App/Commands';
        $this->commands          = [];
        $this->command_class     = 'Default_Handler';
        $this->Event             = new Event_Handler;
    }

    /**
     * Load an Application instance
     *
     * @param Command_Container_Interface $Command_Container
     *
     * @return Application
     */
    public static function load(Command_Container_Interface $Command_Container): Application
    {
        if (is_null(self::$instance)) {
            self::$instance = new Application($Command_Container);
        }

        return self::$instance;
    }

    /**
     * Read the Application instance or return null
     *
     * @return mixed
     */
    public static function read()
    {
        return self::$instance ?? null;
    }

    /**
     * Get the command Container
     *
     * @throws Error
     *
     * @return Command_Container_Interface
     */
    public static function getCommandContainer(): Command_Container_Interface
    {
        if (is_null(self::$instance)) {
            throw new Error('Application is not yet instantiated.');
        }

        return self::$instance->Command_Container;
    }

    /**
     * Set a command and it's action
     *
     * @param string $command_name
     * @param mixed $action
     *
     * @return void
     */
    public function set(string $command_name, $action): void
    {
        if (empty($action)) {
            return;
        }
        $this->commands[$command_name] = $action;

        return;
    }

    /**
     * Create alias's for commands
     *
     * @param array  $alias
     * @param string $action
     *
     * @return void
     */
    public function alias(array $alias, string $action): void
    {
        foreach ($alias as $value) {
            $this->set($value, $action);
        }

        return;
    }

    /**
     * Run the application
     *
     * @return void
     */
    public function run(): void
    {
        // Gets the passed command
        $command = $this->Command_Container->get('command');

        // If this command is a set command figure it our here
        if (!empty($this->commands[$command])) {
            $action = $this->commands[$command];

            if (empty($action)) {
                return;
            }

            // passed a function in
            if (is_callable($action)) {
                call_user_func($action);
                return;
            }

            // passed a class @ method ex: App\Commands\Hello\Test@helloWorld
            if (strpos($action, '@') !== false) {
                $parts = explode('@', $action);
                $c     = new $parts[0];

                if (method_exists($c, $parts[1])) {
                    call_user_func([$c, $parts[1]]);
                }

                return;
            }
        }

        // not explicitly set, find in commands dir
        $this->findCommand();
    }

    /**
     * Figure out what file we need to run in the Commands Dir
     *
     * @return void
     */
    public function findCommand(): void
    {
        // Get the command
        $command = ucwords($this->Command_Container->get('command'));

        // if the command has a dash we need to do some processing.
        if (strpos($command, '-')) {
            $command = $this->parseCommand($command);
        }

        if ($this->Command_Container->has('sub_command')) {
            $this->command_class = ucwords($this->Command_Container->get('sub_command'));

            if (strpos($this->command_class, '-')) {
                $this->command_class = $this->parseCommand($this->command_class);
            }
        }

        $className = sprintf("App\Commands\%s\%s", $command, $this->command_class);

        if (class_exists($className)) {
            $c = new $className();
            $this->Command_Validator->processPassedArgs('flags', $c->flags ?? []);
            $this->Command_Validator->processPassedArgs('params', $c->parameters ?? []);
            $this->Command_Validator->checkRequiredParams($c->required_parameters ?? []);
            $c->handle();
            return;
        }

        Output::error("ERROR!!!!!\nCommand Not Found!");
        return;
    }

    /**
     * Parse a command replacing - with _ and ucwords on all words
     *
     * @param string $command
     * @return string
     */
    public function parseCommand(string $command): string
    {
        $parts = explode('-', $command);
        foreach ($parts as &$v) {
            $v = ucwords($v);
        }

        $command = implode('_', $parts);

        return $command;
    }

    /**
     * __get magic method
     *
     * @param string $arg
     *
     * @return mixed
     */
    public function __get(string $arg)
    {
        if (property_exists($this->Command_Container, $arg)) {
            return $this->Command_Container->$arg;
        }

        return null;
    }

    /**
     * __call magic method
     *
     * @param string $arg
     * @param array $params
     *
     * @return void
     */
    public function __call(string $arg, array $params)
    {
        if (method_exists($this->Command_Container, $arg)) {
            return call_user_func_array([$this->Command_Container, $arg], $params);
        }

        return null;
    }
}
