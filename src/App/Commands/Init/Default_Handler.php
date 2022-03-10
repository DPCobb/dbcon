<?php
namespace App\Commands\Init;

use App\Core\Application;
use App\Interfaces\Command_Handler_Interface;
use App\IO\Output;
use App\IO\Input;

class Default_Handler implements Command_Handler_Interface
{
    /**
     * flags
     *
     * These are the flags allowed to be used with this command
     *
     * @var array
     */
    public array $flags = ['v', 'verbose'];

    /**
     * parameters
     *
     * The parameters this command accepts
     *
     * @var array
     */
    public array $parameters = [];

    /**
     * required_parameters
     *
     * Any parameters this command requires to be set
     *
     * @var array
     */
    public array $required_parameters = [];

    /**
     * ev
     *
     * event handler variable
     *
     * @var object
     */
    public object $ev;

    /**
     * cc
     *
     * Command_Container
     *
     * @var object
     */
    public object $cc;

    /**
     * is_verbose
     *
     * @var boolean
     */
    public bool $is_verbose;

    public function __construct()
    {
        $this->ev         = Application::read()->Event;
        $this->cc         = Application::getCommandContainer();
        $this->is_verbose = ($this->cc->hasFlag('v') || $this->cc->hasFlag('verbose'));
    }

    /**
     * Call an event dispatch if we are using verbose output
     *
     * @param string $event
     *
     * @return void
     */
    public function trigger(string $event): void
    {
        if (!empty($event) && $this->is_verbose) {
            $this->ev->dispatch($event);
        }
    }

    /**
     * Default entry point into our command
     *
     * @return void
     */
    public function handle()
    {
        if (file_exists('dbcon.json')) {
            Output::warning('You have already initiated this project with DBCon. Exiting.');
            $this->trigger('script-end');
            exit;
        }

        $this->makeConfigFile();
    }

    /**
     * Makes our configuration file
     *
     * @return void
     */
    public function makeConfigFile(): void
    {
        $version_path   = Input::get('Where will your DB Version files be stored? (Default: ' . ROOT . '/db_con)') ?: ROOT . '/db_con';
        $version_prefix = Input::get('What prefix should be used on version files? (Default: Version)') ?: 'Version';
        $db_user        = Input::get('What is your database user?');
        $db_password    = Input::get('What is your database password');
        $db_name        = Input::get('What is your database name?');
        $db_host        = Input::get('What is your database host?');

        Output::info("Version Path: $version_path");
        Output::info("Version Prefix: $version_prefix");
        Output::info("Database User: $db_user");
        Output::info("Database Password: $db_password");
        Output::info("Database Name: $db_name");
        Output::info("Database Host: $db_host");

        // Make sure this should be saved
        if (!Input::affirm('Save these settings to dbcon.json? Y/n')) {
            return;
        }

        Output::warning('Add dbcon.json to your repository ignore file!!!!');

        $data = json_encode([
            'version_path'   => $version_path,
            'version_prefix' => $version_prefix,
            'db_user'        => $db_user,
            'db_password'    => $db_password,
            'db_name'        => $db_name,
            'db_host'        => $db_host
        ]);

        file_put_contents(ROOT . '/dbcon.json', $data);

        Output::success('File Created!');

        $this->trigger('script-end');

        return;
    }
}
