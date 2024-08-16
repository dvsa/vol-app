<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Export difference betweeen company house and olcs data to csv (excel)
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
final class CompaniesHouseVsOlcsDiffsExport extends AbstractCommand
{
    /** @var string|null */
    protected $path = null;

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
