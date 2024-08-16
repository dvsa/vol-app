<?php

/**
 * Update Short Notice
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateShortNotice as UpdateShortNoticeCmd;

/**
 * Update Short Notice
 */
final class UpdateShortNotice extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'BusShortNotice';

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateShortNoticeCmd $command */
        /** @var BusShortNotice $shortNotice */

        $result = new Result();

        $shortNotice = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $shortNotice->update(
            $command->getBankHolidayChange(),
            $command->getUnforseenChange(),
            $command->getUnforseenDetail(),
            $command->getTimetableChange(),
            $command->getTimetableDetail(),
            $command->getReplacementChange(),
            $command->getReplacementDetail(),
            $command->getNotAvailableChange(),
            $command->getNotAvailableDetail(),
            $command->getSpecialOccasionChange(),
            $command->getSpecialOccasionDetail(),
            $command->getConnectionChange(),
            $command->getConnectionDetail(),
            $command->getHolidayChange(),
            $command->getHolidayDetail(),
            $command->getTrcChange(),
            $command->getTrcDetail(),
            $command->getPoliceChange(),
            $command->getPoliceDetail()
        );

        $this->getRepo()->save($shortNotice);
        $result->addId('id', $shortNotice->getId());
        $result->addMessage('Saved successfully');
        return $result;
    }
}
