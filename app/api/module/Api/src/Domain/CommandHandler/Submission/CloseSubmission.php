<?php

/**
 * Close a case
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Command\Cases\CloseSubmission as CloseSubmissionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Close a Submission
 */
final class CloseSubmission extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Submission';

    /**
     * Close a Submission
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var CloseSubmissionCmd $command **/
        /** @var SubmissionEntity $submission **/
        $result = new Result();

        $submission = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $submission->close();

        $this->getRepo()->save($submission);
        $result->addMessage('Submission closed');
        $result->addId('submission', $submission->getId());

        return $result;
    }
}
