<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as SlaExceptionEntity;
use Dvsa\Olcs\Api\Entity\Pi\PiSlaException as PiSlaExceptionEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreatePiSlaException as CreatePiSlaExceptionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

final class CreateSlaException extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PiSlaException';
    protected $extraRepos = ['SlaException', 'Cases'];

    /**
     * Handle command to create a PI SLA Exception
     *
     * @param CreatePiSlaExceptionCmd $command Command
     * @return Result
     * @throws NotFoundException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $result = new Result();

        // Get the case ID from the command
        $caseId = $command->getCase();

        /** @var CaseEntity $case */
        $case = $this->getRepo('Cases')->fetchById($caseId);
        if (!$case) {
            throw new NotFoundException('Case not found: ' . $caseId);
        }

        // Validate that the case has a public inquiry
        $pi = $case->getPublicInquiry();
        if (!$pi) {
            throw new NotFoundException('Case does not have a public inquiry: ' . $caseId);
        }

        /** @var SlaExceptionEntity $slaException */
        $slaException = $this->getRepo('SlaException')->fetchById($command->getSlaException());
        if (!$slaException) {
            throw new NotFoundException('SLA Exception not found');
        }

        // Users can add multiple SLA exceptions to a single PI
        // Create new PI SLA Exception relationship
        $piSlaException = new PiSlaExceptionEntity($pi, $slaException);

        $this->getRepo()->save($piSlaException);

        $result->addMessage('Case PI SLA exception added successfully');
        $result->addId('case', $case->getId());
        $result->addId('slaException', $slaException->getId());
        $result->addId('caseSlaException', $piSlaException->getId());

        return $result;
    }
}
