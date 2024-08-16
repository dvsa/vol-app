<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage;

use DateTime;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\SystemInfoMessage;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for CREAT a System info message
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'SystemInfoMessage';

    /**
     * @param Command\System\InfoMessage\Create $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(Command\CommandInterface $command)
    {
        $entity = (new SystemInfoMessage())
            ->setDescription($command->getDescription())
            ->setStartDate(new DateTime($command->getStartDate()))
            ->setEndDate(new DateTime($command->getEndDate()))
            ->setIsInternal($command->getIsInternal());

        $this->getRepo()->save($entity);

        $id = $entity->getId();

        return $this->result
            ->addId('systemInfoMessage', $id)
            ->addMessage("System Info Message '{$id}' created");
    }
}
