<?php

/**
 * Update SubmissionSectionComment
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionSectionComment as Cmd;
use Dvsa\Olcs\Transfer\Command\Submission\DeleteSubmissionSectionComment as DeleteCommentCmd;

/**
 * Update SubmissionSectionComment
 */
final class UpdateSubmissionSectionComment extends AbstractCommandHandler
{
    protected $repoServiceName = 'SubmissionSectionComment';

    /**
     * Updates the submission comment, or deletes it if empty
     *
     * @param CommandInterface|Cmd $command Command to update the submission comment
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        //if comment is empty then delete it instead
        if (empty($command->getComment())) {
            $result->merge($this->handleSideEffect(DeleteCommentCmd::create(['id' => $command->getId()])));
            return $result;
        }

        /**
         * @var SubmissionSectionComment $submissionSectionComment
         */
        $submissionSectionComment = $this->getRepo()->fetchUsingId(
            $command,
            Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        $submissionSectionComment->setComment($command->getComment());
        $this->getRepo()->save($submissionSectionComment);

        $result->addId('submissionSectionComment', $submissionSectionComment->getId());
        $result->addId('submissionSection', $submissionSectionComment->getSubmissionSection()->getId());
        $result->addMessage('Submission section comment updated successfully');

        return $result;
    }
}
