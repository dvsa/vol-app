<?php

/**
 * Command Sender Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Cqrs\Command;

/**
 * Command Sender Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface CommandSenderAwareInterface
{
    /**
     * Set Command sender
     */
    public function setCommandSender(CommandSender $CommandSender);

    /**
     * Get Command sender
     *
     * @return CommandSender
     */
    public function getCommandSender();
}
