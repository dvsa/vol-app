<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System\PublicHoliday;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\PublicHoliday;
use Dvsa\Olcs\Transfer\Command;

/**
 * Handler for CREATE a Public holiday
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'PublicHoliday';

    /**
     * @param Command\System\PublicHoliday\Create $command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(Command\CommandInterface $command)
    {
        $entity = new PublicHoliday(
            new DateTime($command->getHolidayDate()),
            $command->getIsEngland(),
            $command->getIsWales(),
            $command->getIsScotland(),
            $command->getIsIreland()
        );

        $this->getRepo()->save($entity);

        $id = $entity->getId();

        return $this->result
            ->addId('publicHoliday', $id)
            ->addMessage("Public Holiday '{$id}' created");
    }
}
