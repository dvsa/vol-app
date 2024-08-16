<?php

namespace Dvsa\Olcs\Cli\Domain\Command;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Import users from CSV file
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
final class ImportUsersFromCsv extends AbstractCommand
{
    /** @var string */
    protected $csvPath = null;

    /** @var string|null */
    protected $resultCsvPath = null;

    /**
     * Path to source csv file
     *
     * @return string
     */
    public function getCsvPath()
    {
        return $this->csvPath;
    }

    /**
     * Path to result csv file
     *
     * @optional
     * @return string|null
     */
    public function getResultCsvPath()
    {
        return $this->resultCsvPath;
    }
}
