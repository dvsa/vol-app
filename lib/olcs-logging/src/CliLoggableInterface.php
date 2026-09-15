<?php

namespace Olcs\Logging;

interface CliLoggableInterface
{
    /**
     * Get the path of the script being executed.
     *
     * @return string The path of the script.
     */
    public function getScriptPath(): string;

    /**
     * Get the command-line parameters passed to the script.
     *
     * @return array The command-line parameters.
     */
    public function getScriptParams(): array;
}
