<?php

/**
 * Set information complete date on a Submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Command\Submission\InformationCompleteSubmission as Cmd;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Set Information complete date for a Submission
 */
final class InformationComplete extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Submission';

    public function handleCommand(CommandInterface $command)
    {
        $submissionEntity = $this->updateSubmission($command);

        $this->getRepo()->save($submissionEntity);

        $result = new Result();
        $result->addId('submission', $submissionEntity->getId());
        $result->addMessage('Submission updated successfully');

        // generate all related SLA Target Dates
        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDateCmd::create(
                    [
                        'submission' => $submissionEntity->getId()
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * @return SubmissionEntity
     */
    private function updateSubmission(Cmd $command)
    {
        /** @var SubmissionEntity $submission */
        $submission = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $submission->setInformationCompleteDate($command->getInformationCompleteDate());

        return $submission;
    }
}
