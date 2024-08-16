<?php

/**
 * Complete queue item
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Queue;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Complete queue item
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Complete extends AbstractCommand
{
    /**
     * @var QueueEntity
     */
    protected $item;

    /**
     * @return QueueEntity
     */
    public function getItem()
    {
        return $this->item;
    }
}
