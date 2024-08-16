<?php

/**
 * Update Prohibition Note
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateProhibitionNote as UpdateProhibitionNoteCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Prohibition Note
 */
final class UpdateProhibitionNote extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Cases';

    /**
     * Updates Prohibition Note in case table
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateProhibitionNoteCmd $command **/
        /** @var CasesEntity $case **/
        $result = new Result();

        $case = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $case->updateProhibitionNote(
            $command->getProhibitionNote()
        );

        $this->getRepo()->save($case);
        $result->addMessage('Prohibition note updated');
        $result->addId('case', $case->getId());

        return $result;
    }
}
