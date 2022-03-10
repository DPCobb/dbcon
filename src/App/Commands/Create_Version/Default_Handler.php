<?php
namespace App\Commands\Create_Version;

use App\Core\Application;
use App\Interfaces\Command_Handler_Interface;
use App\IO\Output;
use App\IO\Input;
use App\Utility\Stored_Settings;

/**
 * Default Handler for create-version command.
 *
 * Creates a new version file.
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
     * stored_settings
     *
     * @var Stored_Settings
     */
    public Stored_Settings $settings;

    /**
     * template
     *
     * @var string
     */
    public string $template;

    /**
     * time
     *
     * @var string
     */
    public string $time;

    public function __construct()
    {
        $this->settings = new Stored_Settings;
        $this->template = file_get_contents(__DIR__ . '/../../template.php');
        $this->time     = gmdate('YmdHis');

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
     * Run the create-version command
     *
     * @return void
     */
    public function handle(): void
    {
        Output::success('Creating new version file...');

        $settings = $this->settings->getSettings();

        $file      = $settings['version_prefix'] . '_' . $this->time;
        $template  = str_replace('{{VERSION_NAME}}', $file, $this->template);
        $save_file = $settings['version_path'] . '/' . $file . '.php';

        if (!is_dir($settings['version_path'])) {
            $make_dir = mkdir($settings['version_path'], 0775);

            if (!$make_dir) {
                Output::error($settings['version_path'] . ' not found and could not be created!');
            }
        }

        file_put_contents($save_file, $template);

        Output::success('File created at: ' . $save_file);
        $this->ev->dispatch('new-version', ['file' => $file]);
        $this->trigger('script-end');
        return;
    }
}
