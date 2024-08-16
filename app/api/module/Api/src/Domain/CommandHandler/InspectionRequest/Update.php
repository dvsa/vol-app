<?php

/**
 * Inspection Request / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Inspection Request / Update
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'InspectionRequest';

    /**
     * @param \Dvsa\Olcs\Transfer\Command\InspectionRequest\Update $command
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $inspectionRequest = $this->updateInspectionRequest($command);
        $this->getRepo()->save($inspectionRequest);

        $result->addId('inspectionRequest', $inspectionRequest->getId());
        $result->addMessage('Inspection request updated successfully');

        return $result;
    }

    /**
     * @param \Dvsa\Olcs\Transfer\Command\InspectionRequest\Update $command
     * @return InspectionRequest
     */
    private function updateInspectionRequest($command)
    {
        $inspectionRequest = $this->getRepo()->fetchById($command->getId());

        $inspectionRequest->updateInspectionRequest(
            $this->getRepo()->getRefdataReference($command->getRequestType()),
            $command->getRequestDate(),
            $command->getDueDate(),
            null,
            $this->getRepo()->getRefdataReference($command->getResultType()),
            $command->getRequestorNotes(),
            $this->getRepo()->getRefdataReference($command->getReportType()),
            null,
            null,
            null,
            null,
            $command->getInspectorName(),
            $command->getReturnDate(),
            $command->getFromDate(),
            $command->getToDate(),
            $command->getVehiclesExaminedNo(),
            $command->getTrailersExaminedNo(),
            $command->getInspectorNotes()
        );
        return $inspectionRequest;
    }
}
