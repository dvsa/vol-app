<?php

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Close expired windows
 */
final class CloseExpiredWindows extends AbstractCommand
{
    /** @var string  */
    protected $since;

    /**
     * @return string
     */
    public function getSince()
    {
        return $this->since;
    }
}
