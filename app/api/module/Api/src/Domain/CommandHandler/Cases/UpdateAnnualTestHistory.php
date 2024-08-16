<?php

/**
 * Update Annual Test History
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateAnnualTestHistory as UpdateAnnualTestHistoryCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Annual Test History
 */
final class UpdateAnnualTestHistory extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Updates Annual Test History
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateAnnualTestHistoryCmd $command **/
        /** @var CasesEntity $case **/
        $result = new Result();

        $case = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $case->updateAnnualTestHistory(
            $command->getAnnualTestHistory()
        );

        $this->getRepo()->save($case);
        $result->addMessage('Annual Test History updated');
        $result->addId('case', $case->getId());

        return $result;
    }
}
