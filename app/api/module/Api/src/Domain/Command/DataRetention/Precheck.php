<?php

namespace Dvsa\Olcs\Api\Domain\Command\DataRetention;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Class Precheck
 */
final class Precheck extends AbstractCommand
{
    protected $limit;

    /**
     * Get limit, number of rows to process
     *
     * @return int
     */
    public function getLimit()
    {
        return (int)$this->limit;
    }
}
