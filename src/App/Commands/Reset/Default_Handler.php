<?php
namespace App\Commands\Reset;

use App\Core\Application;
use App\Interfaces\Command_Handler_Interface;
use App\IO\Output;
use App\IO\Input;
use App\Utility\Stored_Settings;

/**
 * Handler for reset command, resets a file from error status
 *
 * @author DC
 */
class Default_Handler implements Command_Handler_Interface
{
    /**
     * flags
     *
     * These are the flags allowed to be used with this command
     *
     * @var array
     */
    public array $flags = ['v', 'verbose', 'F', 'force'];

    /**
     * parameters
     *
     * The parameters this command accepts
     *
     * @var array
     */
    public array $parameters = ['file'];

    /**
     * required_parameters
     *
     * Any parameters this command requires to be set
     *
     * @var array
     */
    public array $required_parameters = ['file'];

    /**
     * ev
     *
     * event handler variable
     *
     * @var object
     */
    public object $ev;

    /**
     * stored_settings
     *
     * @var Stored_Settings
     */
    public Stored_Settings $settings;

    /**
     * time
     *
     * @var string
     */
    public string $time;

    public function __construct()
    {
        $this->settings   = new Stored_Settings;
        $this->time       = gmdate('YmdHis');
        $this->ev         = Application::read()->Event;
        $this->cc         = Application::getCommandContainer();
        $this->is_verbose = ($this->cc->hasFlag('v') || $this->cc->hasFlag('verbose'));
        $this->force      = ($this->cc->hasFlag('F') || $this->cc->hasFlag('force'));
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
     * Run command
     *
     * @return void
     */
    public function handle(): void
    {
        $file = $this->cc->get('params.file');

        if (strpos($file, '.php') !== false) {
            $file = str_replace('.php', '', $file);
        }

        $settings     = $this->settings->getSettings();
        $version_file = $settings['version_files'][$file];

        if ($version_file !== 'error' && $this->force === false) {
            Output::warning('File cannot be reset, its status is not an error.');
            $this->trigger('script-end');
            return;
        }

        $settings = $this->settings->getSettings();

        if ($this->is_verbose) {
            Output::warning("Resetting file: $file");
        }
        $settings['version_files'][$file] = false;
        $this->settings->save($settings);
        $this->trigger('script-end');
        return;
    }
}
