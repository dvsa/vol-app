<?php

namespace Dvsa\Olcs\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Abstract stock id command
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
abstract class AbstractStockIdCommand extends AbstractCommand
{
    /**
     * @var int
     */
    protected $stockId;

    /**
     * Gets the value of stockId
     *
     * @return int
     */
    public function getStockId()
    {
        return $this->stockId;
    }
}
