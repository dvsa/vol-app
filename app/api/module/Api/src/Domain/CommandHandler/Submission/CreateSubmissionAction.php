<?php

/**
 * Create SubmissionAction
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionAction;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction as Cmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create SubmissionAction
 */
final class CreateSubmissionAction extends AbstractCommandHandler
{
    protected $repoServiceName = 'SubmissionAction';

    public function handleCommand(CommandInterface $command)
    {
        $submissionAction = $this->createSubmissionAction($command);

        $this->getRepo()->save($submissionAction);

        $result = new Result();
        $result->addId('submissionAction', $submissionAction->getId());
        $result->addMessage('Submission Action created successfully');

        return $result;
    }

    /**
     * @return SubmissionAction
     */
    private function createSubmissionAction(Cmd $command)
    {
        // Backend validate
        if (
            !empty(
                array_intersect(
                    $command->getActionTypes(),
                    [
                    SubmissionAction::ACTION_TYPE_PUBLIC_INQUIRY,
                    SubmissionAction::ACTION_TYPE_TM_PUBLIC_INQUIRY,
                    SubmissionAction::ACTION_TYPE_PROPOSE_TO_REVOKE
                    ]
                )
            )
            && empty($command->getReasons())
            && ($command->getIsDecision() === 'N')
        ) {
            throw new ValidationException(
                [
                    'actionTypes' => [
                        SubmissionAction::ERROR_ACTION_REQUIRES_LEGISLATION
                    ]
                ]
            );
        }

        $actionTypes = array_map(
            fn($actionTypeId) => $this->getRepo()->getRefdataReference($actionTypeId),
            $command->getActionTypes()
        );

        $submissionAction = new SubmissionAction(
            $this->getRepo()->getReference(Submission::class, $command->getSubmission()),
            $command->getIsDecision(),
            $actionTypes,
            $command->getComment()
        );

        if ($command->getReasons() !== null) {
            $reasons = array_map(
                fn($reasonId) => $this->getRepo()->getReference(Reason::class, $reasonId),
                $command->getReasons()
            );
            $submissionAction->setReasons($reasons);
        }

        return $submissionAction;
    }
}
