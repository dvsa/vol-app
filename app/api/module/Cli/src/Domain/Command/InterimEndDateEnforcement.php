<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class InterimEndDateEnforcement extends AbstractCommand
{
    protected bool $dryRun = false;

    /**
     * Commit changes to the database; or dry run echoing out changes but not actually committing them.
     *
     * Defaults to FALSE.
     *
     * @return bool
     */
    public function getDryRun(): bool
    {
        return $this->dryRun;
    }
}
