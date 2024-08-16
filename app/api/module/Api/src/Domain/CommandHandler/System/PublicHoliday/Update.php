<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for UPDATE a Public holiday
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'PublicHoliday';

    /**
     * @param Command\System\PublicHoliday\Update $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(Command\CommandInterface $command)
    {
        /** @var \Dvsa\Olcs\Api\Entity\System\PublicHoliday $entity */
        $entity = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $entity->update(
            new DateTime($command->getHolidayDate()),
            $command->getIsEngland(),
            $command->getIsWales(),
            $command->getIsScotland(),
            $command->getIsIreland()
        );

        $this->getRepo()->save($entity);

        return $this->result->addMessage("Public Holiday '{$entity->getId()}' updated");
    }
}
