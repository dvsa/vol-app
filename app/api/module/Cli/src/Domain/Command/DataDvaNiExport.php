<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Export data to csv Northern Ireland
 *
 */
final class DataDvaNiExport extends AbstractCommand
{
    /** @var string  */
    protected $reportName = null;

    /**
     * @return string
     */
    public function getReportName()
    {
        return $this->reportName;
    }
}
