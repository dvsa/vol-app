<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System\InfoMessage;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for UPDATE a System info message
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'SystemInfoMessage';

    /**
     * @param Command\System\InfoMessage\Update $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(Command\CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Entity\System\SystemInfoMessage $entity */
        $entity = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $entity
            ->setDescription($command->getDescription())
            ->setStartDate(new DateTime($command->getStartDate()))
            ->setEndDate(new DateTime($command->getEndDate()))
            ->setIsInternal($command->getIsInternal());

        $this->getRepo()->save($entity);

        return $this->result->addMessage("System Info Message '{$entity->getId()}' updated");
    }
}
