<?php
namespace App\Commands\Run;

use App\Core\Application;
use App\Interfaces\Command_Handler_Interface;
use App\IO\Output;
use App\Utility\Database;
use App\Utility\Stored_Settings;

/**
 * Handler for run revert command
 *
 * @author DC
 */
class Revert implements Command_Handler_Interface
{
    /**
     * flags
     *
     * These are the flags allowed to be used with this command
     *
     * @var array
     */
    public array $flags = ['v', 'verbose', 'd', 'dryRun'];

    /**
     * parameters
     *
     * The parameters this command accepts
     *
     * @var array
     */
    public array $parameters = ['name'];

    /**
     * required_parameters
     *
     * Any parameters this command requires to be set
     *
     * @var array
     */
    public array $required_parameters = ['name'];

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
        $this->auto_yes   = ($this->cc->hasFlag('A') || $this->cc->hasFlag('auto'));
        $db_cred          = $this->settings->getSettings();
        $this->db         = new Database($db_cred['db_user'], $db_cred['db_password'], $db_cred['db_name'], $db_cred['db_host']);
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
        $file = $this->cc->get('params.name');

        if (strpos($file, '.php') !== false) {
            $file = str_replace('.php', '', $file);
        }

        $settings     = $this->settings->getSettings();
        $version_file = $settings['version_files'][$file];

        if ($version_file === false) {
            Output::error("This file cannot be reverted because it has not yet been ran! $file");
            $this->trigger('script-end');
            return;
        }

        if ($version_file === 'error') {
            Output::error("This file cannot be reverted because its last run resulted in an error! $file");
            $this->trigger('script-end');
            return;
        }

        $file_name = "$file.php";
        $file_path = $settings['version_path'];
        $version   = "$file_path/$file_name";

        if (!file_exists("$version")) {
            Output::warning("Cannot find version file to run: $version");
            $this->setFileError($file);
            $this->trigger('script-end');
            return;
        }

        include_once $version;
        $ver    = new $file;
        $sql    = $ver->revert();
        $result = $this->db->runQuery($sql, $this->auto_yes);
        $this->updateFile($file, $result);
        $this->trigger('script-end');
        return;
    }

    /**
     * Manually sets a file status to error
     *
     * @param string $file
     *
     * @return void
     */
    public function setFileError(string $file)
    {
        $this->updateFile($file, ['status' => 'error']);
    }

    /**
     * Update file status
     *
     * @param string $file
     * @param array $result
     *
     * @return void
     */
    public function updateFile(string $file, array $result)
    {
        $settings = $this->settings->getSettings();

        $settings['version_files'][$file] = $result['status'];

        $this->settings->save($settings);
    }
}
