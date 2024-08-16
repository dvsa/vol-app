<?php

/**
 * Update Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\UpdateFinancialHistory as Cmd;

/**
 * Update Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class UpdateFinancialHistory extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $application->updateFinancialHistory(
            $command->getBankrupt(),
            $command->getLiquidation(),
            $command->getReceivership(),
            $command->getAdministration(),
            $command->getDisqualified(),
            $command->getInsolvencyDetails(),
            $command->getInsolvencyConfirmation()
        );

        $this->getRepo()->save($application);
        $result->addMessage('Financial history section has been updated');

        $data = [
            'id' => $command->getId(),
            'section' => 'financialHistory'
        ];
        $result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        return $result;
    }
}
