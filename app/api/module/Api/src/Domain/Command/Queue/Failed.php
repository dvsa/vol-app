<?php

namespace Dvsa\Olcs\Api\Domain\Command\Queue;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Failed queue item
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Failed extends AbstractCommand
{
    /**
     * @var QueueEntity
     */
    protected $item;

    /**
     * @var string
     */
    protected $lastError;

    /**
     * Return queue entity
     *
     * @return QueueEntity
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Returns last error message
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }
}
