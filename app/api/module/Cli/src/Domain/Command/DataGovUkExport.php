<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Export data to csv for data.gov.uk
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
final class DataGovUkExport extends AbstractCommand
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
