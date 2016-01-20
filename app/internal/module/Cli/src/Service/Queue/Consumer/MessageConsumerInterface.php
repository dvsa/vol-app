<?php

/**
 * Message Consumer Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue\Consumer;

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
     * @param array $item
     * @return boolean
     */
    public function processMessage(array $item);
}
