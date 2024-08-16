<?php

/**
 * Update Conviction Note
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateConvictionNote as UpdateConvictionNoteCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Conviction Note
 */
final class UpdateConvictionNote extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Updates Conviction Note in case table
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateConvictionNoteCmd $command **/
        /** @var CasesEntity $case **/
        $result = new Result();

        $case = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $case->updateConvictionNote(
            $command->getConvictionNote()
        );

        $this->getRepo()->save($case);
        $result->addMessage('Conviction note updated');
        $result->addId('case', $case->getId());

        return $result;
    }
}
