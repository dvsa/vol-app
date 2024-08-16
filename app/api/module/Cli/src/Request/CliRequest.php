<?php

namespace Dvsa\Olcs\Cli\Request;

use Laminas\Http\PhpEnvironment\Request;
use Olcs\Logging\CliLoggableInterface;

class CliRequest extends Request implements CliLoggableInterface
{
    public function getScriptPath(): string
    {
        return $_SERVER['argv'][0] ?? 'unknown';
    }

    public function getScriptParams(): array
    {
        return array_slice($_SERVER['argv'], 1);
    }
}
