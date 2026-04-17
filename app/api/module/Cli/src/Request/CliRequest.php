<?php

namespace Dvsa\Olcs\Cli\Request;

use Laminas\Http\PhpEnvironment\Request;
use Olcs\Logging\CliLoggableInterface;

class CliRequest extends Request implements CliLoggableInterface
{
    #[\Override]
    public function getScriptPath(): string
    {
        return $_SERVER['argv'][0] ?? 'unknown';
    }

    #[\Override]
    public function getScriptParams(): array
    {
        return array_slice($_SERVER['argv'], 1);
    }
}
