<?php

/**
 * Message Consumer Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue;

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

    /**
     * Called when processing the message was successful
     *
     * @param array $item
     * @return string
     */
    public function processSuccess(array $item);

    /**
     * Called when processing the message failed
     *
     * @param array $item
     * @param mixed $reason
     * @return string
     */
    public function processFailure(array $item, $reason = null);
}
