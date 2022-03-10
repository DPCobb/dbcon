<?php

namespace App\IO;

use App\IO\Output;

class Input
{
    /**
     * Outputs a message and returns a string from STDIN
     *
     * @param string $message
     *
     * @return mixed
     */
    public static function get(string $message)
    {
        Output::message($message);
        return readline();
    }

    /**
     * Outputs a message and reads input checking for a yes value
     *
     * @param string $message
     *
     * @return boolean
     */
    public static function affirm(string $message): bool
    {
        Output::message($message);
        $response = readline();

        preg_match("/^(y|Y|yes|YES|Yes|True|true)\b/m", $response, $match);
        
        return !empty($match);
    }

    /**
     * Stricter affirm that can require a specific answer
     *
     * @param string $message
     * @param array $required
     *
     * @return boolean
     */
    public static function warnAffirm(string $message, array $required = []): bool
    {
        Output::warning($message);
        $response = readline();

        if (!empty($required)) {
            // if response is not one of the required answers output a warning and try again
            if (!in_array($response, $required)) {
                $second_try = Input::get("Please enter one of the following answers: " . implode('/', $required));
                if (!in_array($second_try, $required)) {
                    Output::error('Your answer could not be processed. Exiting now.');
                    die;
                }
            }
        }

        preg_match("/^(y|Y|yes|YES|Yes|True|true)\b/m", $response, $match);
        
        return !empty($match);
    }
}
