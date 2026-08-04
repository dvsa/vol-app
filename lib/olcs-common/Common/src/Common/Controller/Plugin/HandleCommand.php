<?php

namespace Common\Controller\Plugin;

use Common\Exception\BailOutException;
use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class HandleCommand
 * @package Common\Controller\Plugin
 */
class HandleCommand extends AbstractPlugin
{
    /**
     * @param CommandSender $commandService
     */
    public function __construct(private CommandSender $commandSender, private FlashMessengerHelperService $fm)
    {
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    public function __invoke(CommandInterface $command)
    {
        try {
            return $this->commandSender->send($command);
        } catch (ResourceConflictException) {
            $this->fm->addConflictError();
            throw new BailOutException('', $this->getController()->redirect()->refresh());
        }
    }
}
