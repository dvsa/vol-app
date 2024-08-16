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

    /** @var string|null  */
    protected $path = null;

    /**
     * @return string
     */
    public function getReportName()
    {
        return $this->reportName;
    }

    /**
     * Optional path to folder where cvs files should be saved
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
