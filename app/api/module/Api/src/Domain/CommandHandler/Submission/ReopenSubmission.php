<?php

/**
 * Reopen a submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Command\Submission\ReopenSubmission as ReopenSubmissionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Reopen a submission
 */
final class ReopenSubmission extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Submission';

    /**
     * Reopen a submission
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ReopenSubmissionCmd $command **/
        /** @var SubmissionEntity $submission **/
        $result = new Result();

        $submission = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $submission->reopen();

        $this->getRepo()->save($submission);
        $result->addMessage('Submission reopened');
        $result->addId('submission', $submission->getId());

        return $result;
    }
}
