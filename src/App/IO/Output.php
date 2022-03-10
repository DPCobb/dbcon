<?php
namespace App\IO;

use App\Interfaces\Printer_Interface;
use App\IO\Themes\Default_Printer;

class Output
{
    /**
     * printer
     *
     * @var array|null
     */
    public ?array $printer;

    /**
     * default_printer
     *
     * @var array
     */
    public array $default_printer;

    public static ?Output $instance = null;

    public function __construct(?Printer_Interface $printer = null)
    {
        // printer sets the color values
        $this->printer = null;
        if (!is_null($printer)) {
            $this->printer = $printer->getThemeSettings();
        }
        $default_printer       = new Default_Printer;
        $this->default_printer = $default_printer->getThemeSettings();
    }

    /**
     * Load a custom printer for static Output calls
     *
     * @param Printer_Interface $printer
     *
     * @return object
     */
    public static function load(Printer_Interface $printer)
    {
        if (is_null(static::$instance)) {
            static::$instance = new Output($printer);
        }

        return static::$instance;
    }

    /**
     * Format output message
     *
     * @param string $message
     * @param string $color
     *
     * @return void
     */
    public function output(string $message, string $color): void
    {
        echo sprintf("\e[%sm%s\e[0m\n", $color, $message);
        return;
    }

    /**
     * Pass a multi line message
     *
     * @param array $lines
     * @param string $type
     *
     * @return void
     */
    public function multiLine(array $lines, string $type): void
    {
        $this->$type(implode("\n", $lines));
    }

    /**
     * Static multi line message call
     *
     * @param array $lines
     * @param string $type
     *
     * @return void
     */
    public static function multi(array $lines, string $type): void
    {
        if (!is_null(static::$instance)) {
            static::$instance->multiLine($lines, $type);
            return;
        }

        $out = new Output;
        $out->multiLine($lines, $type);
        return;
    }

    /**
     * Parse a file for output, good for static output ie help pages
     *
     * @param string $filename
     * @param array $variables
     *
     * @return void
     */
    public function parseFile(string $filename, array $variables = []): void
    {
        if (!file_exists($filename)) {
            throw new Error("Cannot find file to parse: $filename");
        }

        $template = file_get_contents($filename);

        $printer = is_null($this->printer) ? $this->default_printer : $this->printer;

        $template = $this->processMessageTypes($template, $printer);
        // Just in case there is a default tag not overwritten in custom printer
        $template = $this->processMessageTypes($template, $this->default_printer);

        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                // uses three because 2 in the template 1 for PHP literal
                $key_tag = "{{{$key}}}";

                $template = str_replace($key_tag, $value, $template);
            }
        }

        echo $template;
        return;
    }

    /**
     * Adds the color codes for specific message types to file
     *
     * @param string $template
     * @param array $printer
     *
     * @return string
     */
    public function processMessageTypes(string $template, array $printer): string
    {
        foreach ($printer as $method => $color) {
            $color_code = sprintf("\e[%sm", $color);
            $method_tag = "<$method>";
            $template   = str_replace($method_tag, $color_code, $template);
        }

        $end_tag  = '<end>';
        $end_code = sprintf("\e[0m");

        $template = str_replace($end_tag, $end_code, $template);

        return $template;
    }

    /**
     * Statically call a file to parse
     *
     * @param string $filename
     * @param array $variables
     *
     * @return void
     */
    public static function file(string $filename, array $variables = []): void
    {
        if (!is_null(static::$instance)) {
            static::$instance->parseFile($filename, $variables);
            return;
        }

        $out = new Output;
        $out->parseFile($filename, $variables);
        return;
    }

    /**
     * Just a new line
     *
     * @return void
     */
    public static function line():void
    {
        echo "\n";
        return;
    }

    /**
     * Full width message in console
     *
     * @param string $message
     *
     * @return string
     */
    public function fullWidthMessage(string $message_in): string
    {
        $width           = (int)shell_exec('tput cols');
        $remaining_width = $width - strlen($message_in);

        $message = $message_in;
        if ($remaining_width >= 0) {
            $message = str_repeat('=', $width);
            $message .= "\n{$message_in}\n";
            $message .= str_repeat('=', $width);
        }

        return $message;
    }

    /**
     * Magic method to process output
     *
     * @param string $method
     * @param array $args
     *
     * @return void
     */
    public function __call(string $method, array $args): void
    {
        $color = $this->printer[$method] ?? $this->default_printer[$method];

        $message = $args[0];

        if ($method === 'banner' || $method === 'error') {
            $message = $this->fullWidthMessage($message);
        }

        $this->output($message, $color);
        $this->line();
        return;
    }

    /**
     * Magic method so you can call these outputs statically
     * Can use theme if Output::load is called first with custom printer
     *
     * @param string $method
     * @param array $args
     *
     * @return void
     */
    public static function __callStatic(string $method, array $args): void
    {
        if (!is_null(static::$instance)) {
            static::$instance->$method($args[0]);
            return;
        }

        $out = new Output;

        $message = $args[0];

        if ($method === 'banner' || $method === 'error') {
            $message = $out->fullWidthMessage($message);
        }

        $out->output($message, $out->default_printer[$method]);
        $out->line();
        return;
    }
}
