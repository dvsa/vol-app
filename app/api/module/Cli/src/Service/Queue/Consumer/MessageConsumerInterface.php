<?php

/**
 * Message Consumer Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Message Consumer Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface MessageConsumerInterface
{
    /**
     * Process the message item
     *
     * @return boolean
     */
    public function processMessage(QueueEntity $item);

    /**
     * Mark the item as failed
     *
     * @param string $reason
     * @return string
     */
    public function failed(QueueEntity $item, $reason = null);
}
